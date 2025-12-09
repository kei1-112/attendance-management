<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        $user = app(CreateNewUser::class)->create($request->all());
        session(['registering_email' => $request->email]);
        event(new Registered($user));
        return redirect()->route('verification.notice');
    }
}
