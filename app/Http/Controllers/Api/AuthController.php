<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
        ]);

        $token = $user->createToken('mobile_token')->plainTextToken;

        return $this->successResponse(
            'Registered successfully.',
            [
                'user' => $user,
                'token' => $token,
            ],
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return $this->successResponse(
            'Logged in successfully.',
            [
                'user' => $user,
                'token' => $token,
            ]
        );
    }

    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $client = new GoogleClient([
            'client_id' => env('GOOGLE_CLIENT_ID'),
        ]);

        $payload = $client->verifyIdToken($data['id_token']);

        if (! $payload) {
            return $this->errorResponse(
                'Invalid Google token.',
                null,
                401
            );
        }

        $googleId = $payload['sub'];
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'Google User';
        $avatar = $payload['picture'] ?? null;
        $emailVerified = $payload['email_verified'] ?? false;

        if (! $email) {
            return $this->errorResponse(
                'Google account email is required.',
                null,
                422
            );
        }

        $user = User::where('provider', 'google')
            ->where('provider_id', $googleId)
            ->first();

        if (! $user) {
            $existingUser = User::where('email', $email)->first();

            if ($existingUser && $existingUser->provider !== 'google') {
                return $this->errorResponse(
                    'This email already exists. Please login with email and password.',
                    null,
                    409
                );
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Str::random(32),
                'role' => 'customer',
                'provider' => 'google',
                'provider_id' => $googleId,
                'avatar' => $avatar,
                'email_verified_at' => $emailVerified ? now() : null,
            ]);
        } else {
            $user->update([
                'name' => $name,
                'avatar' => $avatar,
                'email_verified_at' => $emailVerified ? now() : $user->email_verified_at,
            ]);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return $this->successResponse(
            'Logged in with Google successfully.',
            [
                'user' => $user,
                'token' => $token,
            ]
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            'Authenticated user.',
            [
                'user' => $request->user(),
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            'Logged out successfully.'
        );
    }
}
