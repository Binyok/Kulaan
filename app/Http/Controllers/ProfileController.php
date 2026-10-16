<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
            'courier' => $user->courier,
            'isUmkm' => $user->hasRole('umkm'),
            'isDistributor' => $user->hasRole('distributor'),
            'isCourier' => $user->hasRole('kurir'),
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
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'delivery_lead_time_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
        ], [], [
            'business_name' => 'nama usaha/perusahaan',
            'business_type' => 'jenis usaha',
            'delivery_lead_time_days' => 'estimasi lama pengiriman',
            'vehicle_type' => 'tipe kendaraan',
            'vehicle_number' => 'nomor polisi kendaraan',
        ]);

        $userUpdate = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            ...($this->passwordUpdate($validated)),
        ];

        if (! empty($validated['email'])) {
            $userUpdate['email'] = $validated['email'];
        }

        $user->update($userUpdate);

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
                'delivery_lead_time_days' => $validated['delivery_lead_time_days'] ?? $user->distributor->delivery_lead_time_days ?? 2,
            ]);

            return;
        }

        if ($user->courier) {
            $user->courier->update([
                'vehicle_type' => $validated['vehicle_type'] ?? $user->courier->vehicle_type,
                'vehicle_number' => $validated['vehicle_number'] ?? $user->courier->vehicle_number,
            ]);
        }
    }
}
