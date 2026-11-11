<?php

namespace App\Features\Auth\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    public function execute(Request $request, string $username, string $password, bool $remember = false): bool
    {
        if (! Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
            return false;
        }

        $request->session()->regenerate();

        return true;
    }
}
