<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'name'  => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            $user = Auth::user();

            if (! $user) {
                return back()->withErrors([
                    'general' => 'User not authenticated.',
                ]);
            }

            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'Something went wrong while updating profile.',
            ]);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'confirmed', 
                Password::defaults(),
            ],
        ]);

        $user = Auth::user();

        if (! $user) {
            return back()->withErrors([
                'general' => 'User not authenticated.',
            ]);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors([
                    'error' => 'Old password does not match.',
                ])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
