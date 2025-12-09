<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function notice(){
        return view('auth.verify-email');
    }

    public function send(Request $request){
        $email = session('registering_email');
        if ($email) {
            $user = User::where('email', $email)->first();
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'すでにメール認証が完了しています');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('message', '認証メールを再送しました');
    }

    public function verify($id, $hash){
        $user = User::findOrFail($id);

        if (! URL::hasValidSignature(request())) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        Auth::login($user);
        return redirect('/attendance');
    }
}
