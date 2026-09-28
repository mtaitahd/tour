<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ListingTitles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingTitleController extends Controller
{
    /**
     * Screen for the headings on the two public listing pages.
     *
     * Everything is stored in the existing `settings` table under a key from
     * ListingTitles::catalogue(). There is deliberately no listing_titles
     * table: a fixed, code-owned set of four slots does not need one, and
     * adding a table for it would break the "don't change the database
     * architecture" constraint.
     *
     * A slot with no row is not an error state, it is the normal state: the
     * catalogue's default is used until someone overrides it. `destroy` is
     * therefore "stop overriding", not "delete something you need to re-add".
     */
    public function index()
    {
        return view('admin.listing-titles.index', [
            'rows' => ListingTitles::rows(),
        ]);
    }

    /**
     * Create or update the override for one slot.
     *
     * The key is validated against the catalogue rather than trusted, so a
     * hand-rolled POST cannot write an arbitrary row into `settings`.
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', Rule::in(array_keys(ListingTitles::catalogue()))],
        ]);

        $key = $request->input('key');
        $meta = ListingTitles::catalogue()[$key];
        $multiline = (bool) ($meta['multiline'] ?? false);

        // The field is named after the slot ("value_pages_listing_title") rather
        // than a bare "value" because all four forms live on one screen: a shared
        // field name would let a validation failure repopulate every form with
        // the text that was submitted for just one of them.
        $field = 'value_' . $key;

        $request->validate([
            $field => ['required', 'string', $multiline ? 'max:2000' : 'max:160'],
        ]);

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => trim($request->input($field)),
                'type' => $multiline ? 'textarea' : 'text',
                'group' => ListingTitles::GROUP,
                'label' => $meta['label'],
                'description' => $meta['description'],
            ],
        );

        return redirect()
            ->route('admin.listing-titles.index')
            ->with('success', $meta['label'] . ' updated.');
    }

    /**
     * Drop the override so the slot falls back to its shipped default.
     */
    public function destroy(string $key)
    {
        abort_unless(ListingTitles::has($key), 404);

        $meta = ListingTitles::catalogue()[$key];
        $deleted = Setting::where('key', $key)->delete();

        return redirect()
            ->route('admin.listing-titles.index')
            ->with(
                $deleted
                    ? 'success'
                    : 'warning',
                $deleted
                    ? $meta['label'] . ' reset to the default: "' . $meta['default'] . '".'
                    : $meta['label'] . ' was already using the default.'
            );
    }
}
