<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * ユーザー登録後のレスポンス処理
     */
    public function toResponse($request)
    {
        return redirect('/email/verify');
    }
}