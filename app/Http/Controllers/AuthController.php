<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        try {
            // Create user in database
            $user = User::create([
                'name' => $request->fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->createdResponse([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'fullName' => $user->name,
                ]
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Registration failed', $e);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->unauthorizedResponse('Invalid credentials');
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'fullName' => $user->name,
                ]
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Login failed', $e);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'fullName' => $user->name,
            ]
        ], 'User retrieved successfully');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray(), 'Email not found');
        }

        try {
            $user = User::where('email', $request->email)->first();

            // In production, send actual email with reset link
            // For now, just return success message
            // TODO: Implement sending reset email

            return $this->successResponse(null, 'Password reset link sent to email');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to process password reset', $e);
        }
    }
}