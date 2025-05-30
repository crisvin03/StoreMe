<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
    
        $changes = [];
    
        $validatedData = $request->validated();
    
        // Track which fields are being changed
        foreach ($validatedData as $key => $value) {
            if ($user->$key !== $value) {
                $changes[] = $key;
            }
        }
    
        $user->fill($validatedData);
    
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $changes[] = 'email';
        }
    
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
    
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
    
            $user->profile_picture = $path;
            $changes[] = 'profile picture';
        }
    
        $user->save();
    
        $message = count($changes)
            ? 'Successfully updated: ' . implode(', ', $changes)
            : 'No changes made.';
    
        return Redirect::route('profile.edit')->with('status', $message);
    }    

    /**
     * Upload/update only the profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();

        $path = $request->file('photo')->store('profile-photos', 'public');

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->profile_picture = $path;
        $user->save();

        return back()->with('status', 'photo-updated');
    }

    /**
 * Delete the user's profile photo and reset to default.
 */
public function deletePhoto(Request $request): RedirectResponse
{
    $user = $request->user();

    if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
        Storage::disk('public')->delete($user->profile_picture);
    }

    $user->profile_picture = null;
    $user->save();

    return back()->with('status', 'photo-deleted');
}


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
