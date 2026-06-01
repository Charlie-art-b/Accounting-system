<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('/login-simple', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/admin')->with('success', 'Login exitoso');
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden',
    ]);
})->name('login.simple');

Route::get('/login-simple', function () {
    return view('login-simple');
})->name('login.simple.form');
