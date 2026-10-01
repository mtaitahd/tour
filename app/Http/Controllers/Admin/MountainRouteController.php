<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use App\Models\MountainRoute;
use Illuminate\Http\Request;

class MountainRouteController extends Controller
{
    public function index()
    {
        return view('admin.mountain-routes.index', [
            'mountains' => Mountain::orderBy('name')->get(),
            'routes' => MountainRoute::with('mountain')->orderBy('mountain_id')->orderBy('order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['mountain_id' => 'required|exists:mountains,id', 'name' => 'required|string|max:255', 'description' => 'nullable|string', 'order' => 'nullable|integer|min:0']);
        MountainRoute::create($data);
        return back()->with('success', 'Route added.');
    }

    public function update(Request $request, MountainRoute $mountainRoute)
    {
        $data = $request->validate(['mountain_id' => 'required|exists:mountains,id', 'name' => 'required|string|max:255', 'description' => 'nullable|string', 'order' => 'nullable|integer|min:0']);
        $mountainRoute->update($data);
        return back()->with('success', 'Route updated.');
    }

    public function destroy(MountainRoute $mountainRoute)
    {
        $mountainRoute->delete();
        return back()->with('success', 'Route removed.');
    }
}
