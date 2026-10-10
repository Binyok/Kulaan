<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:umkm,distributor,kurir'],
        ]);

        DB::transaction(function () use ($request, &$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => null,
                'address' => null,
            ]);

            $role = $request->role;
            $user->assignRole($role);

            // Profil detail dilengkapi di halaman Lengkapi Profil setelah registrasi.
            if ($role === UserRole::UMKM->value) {
                Umkm::create([
                    'user_id' => $user->id,
                    'business_name' => $request->name,
                    'address' => '',
                ]);
            } elseif ($role === UserRole::DISTRIBUTOR->value) {
                Distributor::create([
                    'user_id' => $user->id,
                    'company_name' => $request->name,
                    'address' => '',
                ]);
            } elseif ($role === UserRole::KURIR->value) {
                Courier::create(['user_id' => $user->id]);
            }
        });

        Auth::login($user);

        return redirect()->route('profile.complete')
            ->with('info', 'Akun berhasil dibuat! Lengkapi data profil usahamu dulu ya.');
    }
}
