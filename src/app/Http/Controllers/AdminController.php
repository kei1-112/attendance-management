<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->action(
                [AdminAttendanceController::class, 'index']
            );
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->withInput();
    }
}
