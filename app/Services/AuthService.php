<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\User;

class AuthService
{
    
    public function register($data){
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('user');
        return $user;
    }

    public function login($credentials){
        if (!$token = auth()->attempt($credentials)) {
         throw new \Exception("Invalid credentials");
        }
        return $token;
    }

    public function me(){
        return auth()->user();
    }
}