<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('admin.profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('admin.profile.show')
                         ->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password'      => ['required', 'current_password:web'], // 'web' guard for session auth
            'password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'password_confirmation' => ['required'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        // In updatePassword() after $user->save();
        Auth::logout();
        return redirect()->route('login')->with('success', 'Password changed successfully! Please log in again.');
    }

    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            // Selected via <x-media-picker> — an existing image id from the Media
            // Library, not an uploaded file.
            'avatar_image_id' => 'required|integer|exists:media,id',
        ]);

        $user = Auth::user();
        $mediaLibrary = app(MediaLibraryService::class);

        // Forget the previous avatar's usage record before recording the new one, so
        // an old, no-longer-used avatar doesn't stay marked "in use" forever and block
        // deletion incorrectly — same convention as hero image swaps elsewhere.
        if ($user->avatar_image_id) {
            $previousAvatar = GalleryImage::find($user->avatar_image_id);
            if ($previousAvatar) {
                $mediaLibrary->forgetUsage($previousAvatar->id, $user, 'avatar_image_id');
            }
        }

        $newAvatar = GalleryImage::find($validated['avatar_image_id']);
        if ($newAvatar) {
            $mediaLibrary->recordUsage($newAvatar->id, $user, 'avatar_image_id');
        }

        $user->avatar_image_id = $validated['avatar_image_id'];
        $user->save();

        return redirect()->route('admin.profile.show')
                         ->with('success', 'Profile picture updated successfully!');
    }
}