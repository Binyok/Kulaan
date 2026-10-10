<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Force a user whose profile is still incomplete (typically a fresh
     * Google/OAuth account) to finish it before reaching any other page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $this->isExempt($request)) {
            return $next($request);
        }

        if (blank($user->phone) || blank($user->address)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Profil belum lengkap.'], 403);
            }

            return redirect()->route('profile.complete')
                ->with('info', 'Lengkapi data profil Anda terlebih dahulu.');
        }

        return $next($request);
    }

    private function isExempt(Request $request): bool
    {
        $name = (string) $request->route()?->getName();

        if ($name === 'logout') {
            return true;
        }

        return str_starts_with($name, 'profile.complete');
    }
}
