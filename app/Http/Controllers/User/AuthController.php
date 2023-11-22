<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function signUp(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password'])
        ]);

        event(new Registered($user));

        $token = $user->createToken('authToken')->plainTextToken;

        Auth::login($user);

        return response()
            ->json($user)
            ->withCookie(cookie('userData', $user, 7 * 24 * 60 * 60, null, null, true, env('APP_ENV') === 'production'))
            ->withCookie(cookie('accessToken', $token, 7 * 24 * 60 * 60, null, null, true, env('APP_ENV') === 'production'));
    }

    public function signIn(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                $token = $user->createToken('authToken')->plainTextToken;

                return response()
                    ->json($user)
                    ->withCookie(cookie('userData', $user, 7 * 24 * 60 * 60, null, null, true, env('APP_ENV') === 'production'))
                    ->withCookie(cookie('accessToken', $token, 7 * 24 * 60 * 60, null, null, true, env('APP_ENV') === 'production'));
            }

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function signOut(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()
                ->json(['message' => 'Successfully logged out'])
                ->cookie('userData', null, 0)
                ->cookie('accessToken', null, 0);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
