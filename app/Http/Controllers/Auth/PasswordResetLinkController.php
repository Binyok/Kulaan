<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Selalu kirim bila emailnya terdaftar, tapi tampilkan respons
        // yang sama persis apa pun hasilnya — halaman ini tidak boleh
        // membocorkan apakah sebuah email terdaftar di Kulaan atau tidak.
        Password::sendResetLink(
            $request->only('email')
        );

        return back()->with('status', 'Kalau email ini terdaftar di Kulaan, tautan atur ulang kata sandi sudah kami kirim. Periksa kotak masuk atau folder spam email kamu, ya.');
    }
}
