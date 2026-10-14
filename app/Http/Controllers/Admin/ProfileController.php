<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.index', [
            'title' => __('Profile'),
            'breadcrumb' => breadcrumb([
                __('Profile') => route('admin.profile')
            ])
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;

        if ($request->avatar_remove == 1) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile_image = $path;
        }

        $user->save();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Profile updated successfully');
    }

    public function password()
    {
        return view('admin.profile.password', [
            'title' => __('Password'),
            'breadcrumb' => breadcrumb([
                __('Password') => route('admin.password')
            ])
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::guard('admin')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Old password is incorrect'
            ])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Password updated successfully');
    }

    public function checkEmailExists(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = User::where('email', $request->email)
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', '!=', $request->id);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists
        ]);
    }

    public function checkMobileExists(Request $request)
    {
        $request->validate([
            'mobile' => 'required'
        ]);

        $exists = User::where('mobile', $request->mobile)
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', '!=', $request->id);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists
        ]);
    }

}

