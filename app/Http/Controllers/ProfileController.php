<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the complete-profile form (also used as the edit profile page).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.complete', [
            'user' => $user,
            'umkm' => $user->umkm,
            'distributor' => $user->distributor,
            'isUmkm' => $user->hasRole('umkm'),
            'isDistributor' => $user->hasRole('distributor'),
        ]);
    }

    /**
     * Persist profile data and (optionally) a new password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
        ], [], [
            'business_name' => 'nama usaha',
            'business_type' => 'jenis usaha',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            ...($this->passwordUpdate($validated)),
        ]);

        $this->syncRoleProfile($user, $validated);

        return redirect()->route('dashboard')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    private function passwordUpdate(array $validated): array
    {
        if (blank($validated['password'] ?? null)) {
            return [];
        }

        return ['password' => Hash::make($validated['password'])];
    }

    /**
     * Mirror the shared fields onto the role-specific profile row, if any.
     */
    private function syncRoleProfile($user, array $validated): void
    {
        $attributes = [
            'address' => $validated['address'],
            'district' => $validated['district'],
            'village' => $validated['village'] ?? null,
            'city' => $validated['city'],
            'province' => $validated['province'] ?? null,
        ];

        if ($user->umkm) {
            $user->umkm->update([
                ...$attributes,
                'business_name' => ($validated['business_name'] ?? null) ?: $user->umkm->business_name,
                'business_type' => ($validated['business_type'] ?? null) ?: $user->umkm->business_type,
            ]);

            return;
        }

        if ($user->distributor) {
            $user->distributor->update([
                ...$attributes,
                'company_name' => ($validated['business_name'] ?? null) ?: $user->distributor->company_name,
            ]);
        }
    }
}
