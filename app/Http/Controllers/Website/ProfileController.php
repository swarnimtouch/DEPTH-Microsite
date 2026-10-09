<?php

namespace App\Http\Controllers\Website;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController
{
    public function edit()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        return view('website.profile.edit', [
            'title' => 'My Profile',
            'doctor' => Auth::guard('web')->user(),
            'country' => Country::orderByRaw("CASE WHEN name = 'India' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $doctor = Auth::guard('web')->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hospital' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'mobile' => [
                'required',
                'digits_between:10,15',
                Rule::unique('users', 'mobile')->ignore($doctor->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($doctor->id),
            ],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('profile_image')) {
            $oldImage = $doctor->getRawOriginal('profile_image');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            $validated['profile_image'] = $request->file('profile_image')->store('uploads/profile', 'public');
        } else {
            unset($validated['profile_image']);
        }

        $doctor->fill($validated);
        $doctor->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully');
    }
}
