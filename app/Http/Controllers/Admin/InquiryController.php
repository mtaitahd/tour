<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inquiry;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inquiry::with('tour')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $inquiries = $query->paginate(20)->withQueryString();

        return view('admin.inquiries.index', compact('inquiries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry)
    {
        $inquiry->load('tour');
        return view('admin.inquiries.show', compact('inquiry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'status'      => 'required|in:pending,contacted,confirmed,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        // Fixed: this previously also checked for 'booking' and 'paid', neither of
        // which exists in the status enum (pending/contacted/confirmed/cancelled) —
        // those branches could never actually run; only 'confirmed' ever could.
        if ($validated['status'] === 'confirmed' && !$inquiry->total_amount && $inquiry->tour_package_id) {
            $tour = $inquiry->tour;

            if ($tour) {
                $validated['total_amount'] = $tour->base_price;
            }
        }

        $inquiry->update($validated);

        return redirect()->route('admin.inquiries.index')
                         ->with('success', 'Inquiry updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')
                         ->with('success', 'Inquiry deleted');
    }
}

