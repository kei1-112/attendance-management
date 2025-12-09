<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class UserController extends Controller
{
    //
    public function index(){
        return view('index');
    }

    public function login(LoginRequest $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->validated();

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (! Auth::user()->hasVerifiedEmail()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'メール認証を完了してください。',
                ])->withInput();
            }

            return redirect()->intended('/attendance');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->withInput();
    }
}
