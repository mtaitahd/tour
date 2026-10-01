<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\Request;

class MountainController extends Controller
{
    public function index()
    {
        return view('admin.mountains.index', ['mountains' => Mountain::withCount('routes')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:mountains,name', 'description' => 'nullable|string']);
        Mountain::create($data);
        return back()->with('success', 'Mountain added.');
    }

    public function update(Request $request, Mountain $mountain)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:mountains,name,' . $mountain->id, 'description' => 'nullable|string']);
        $mountain->update($data);
        return back()->with('success', 'Mountain updated.');
    }

    public function destroy(Mountain $mountain)
    {
        $mountain->delete();
        return back()->with('success', 'Mountain and its routes removed.');
    }
}
