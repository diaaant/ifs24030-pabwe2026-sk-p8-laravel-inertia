<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    /** @param array{name: string, username: string, password: string, email?: ?string} $data */
    public function execute(Request $request, array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => 'member',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $user;
    }
}
