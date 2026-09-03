<?php

namespace App\Http\Controllers\PackageEditor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Phase 0 placeholder dashboard for the Package Editor panel. The full editor
 * package workflows arrive in the dedicated Package Editor workflow phase.
 *
 * Only the authorizing editor's own lightweight summary is shown here.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $editor */
        $editor = request()->user();

        return view('editor.dashboard.index', [
            'editor' => $editor,
        ]);
    }
}