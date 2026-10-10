<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect user to Google OAuth page or auto-mock in local if credentials are dummy.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        $clientId = config('services.google.client_id');

        // Simulasi login Google hanya di environment non-production dan hanya
        // ketika kredensial Google Cloud Console belum dikonfigurasi.
        if (! app()->isProduction() && (empty($clientId) || str_contains($clientId, 'dummy-google-client-id'))) {
            return $this->loginWithSimulatedGoogleUser();
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->with('error', 'Login Google sedang tidak tersedia. Silakan gunakan email dan kata sandi.');
        }
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('error', 'Gagal memverifikasi akun Google. Silakan coba lagi.');
        }

        $user = $this->findOrCreateUser($googleUser->getId(), $googleUser->getEmail(), $googleUser->getName(), $googleUser->getAvatar());

        Auth::login($user);

        $request->session()->forget('url.intended');

        return $this->redirectAfterLogin($user);
    }

    /**
     * Development mock helper when Google Console credentials are not yet configured.
     */
    private function loginWithSimulatedGoogleUser(): RedirectResponse
    {
        // Gunakan akun demo Google untuk pengetesan lokal yang mulus
        $dummyGoogleId = 'google_demo_1092837465';
        $email = 'umkm.google.demo@gmail.com';
        $name = 'Budi Santoso (Google Account)';
        $avatar = 'https://ui-avatars.com/api/?name=Budi+Santoso&background=2D6A4F&color=fff';

        $user = $this->findOrCreateUser($dummyGoogleId, $email, $name, $avatar);

        Auth::login($user);

        return $this->redirectAfterLogin($user)
            ->with('success', 'Berhasil masuk menggunakan akun Google (Mode Simulasi Development)');
    }

    /**
     * Send the user to the dashboard, or to profile completion when data is missing.
     */
    private function redirectAfterLogin(User $user): RedirectResponse
    {
        if (blank($user->phone) || blank($user->address)) {
            return redirect()->route('profile.complete');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Find existing user by google_id or email, or create new.
     */
    private function findOrCreateUser(string $googleId, string $email, ?string $name, ?string $avatar): User
    {
        return DB::transaction(function () use ($googleId, $email, $name, $avatar) {
            $user = User::where('google_id', $googleId)->first();

            if (! $user) {
                // Link with existing email account if already present
                $user = User::where('email', $email)->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $avatar ?? $user->avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $name ?? 'Pengguna Google',
                        'email' => $email,
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                        'password' => Hash::make(Str::random(24)),
                        'phone' => null,
                        'address' => null,
                    ]);

                    $user->assignRole(UserRole::UMKM->value);

                    Umkm::create([
                        'user_id' => $user->id,
                        'business_name' => $name ?? 'UMKM Google',
                        'business_type' => 'Kuliner',
                        'address' => 'Jl. Pangeran Hidayatullah No. 10',
                        'district' => 'Banjarmasin Timur',
                        'city' => 'Banjarmasin',
                        'latitude' => -3.315000,
                        'longitude' => 114.605000,
                    ]);
                }
            } else {
                if ($avatar && $user->avatar !== $avatar) {
                    $user->update(['avatar' => $avatar]);
                }
            }

            return $user;
        });
    }
}
