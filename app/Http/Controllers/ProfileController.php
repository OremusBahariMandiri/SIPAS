<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user()->load(['perusahaan', 'departemen']);

        return view('settings.profile', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'email'         => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'nama_karyawan' => ['required', 'string', 'max:100'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.email'            => 'Please enter a valid email address.',
            'email.unique'           => 'This email is already used by another account.',
            'nama_karyawan.required' => 'Full name is required.',
            'password.min'           => 'Password must be at least 8 characters.',
            'password.confirmed'     => 'Password confirmation does not match.',
        ]);

        $data = [
            'email'         => $request->email ?: null,
            'nama_karyawan' => $request->nama_karyawan,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}