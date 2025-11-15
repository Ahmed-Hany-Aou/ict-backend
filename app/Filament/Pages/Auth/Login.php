<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        try {
            $data = $this->form->getState();

            // Get the user by email
            $user = $this->getUserByEmail($data['email']);

            if (!$user) {
                throw ValidationException::withMessages([
                    'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            // Check if password is correct
            // Support both plain text password and direct hash comparison
            $isPlainTextPassword = Hash::check($data['password'], $user->password);
            $isDirectHashMatch = ($data['password'] === $user->password);

            if (!$isPlainTextPassword && !$isDirectHashMatch) {
                throw ValidationException::withMessages([
                    'data.password' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            // Authenticate the user
            auth()->login($user, $data['remember'] ?? false);

            session()->regenerate();

            return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    protected function getUserByEmail(string $email)
    {
        return app(config('auth.providers.users.model'))->where('email', $email)->first();
    }
}
