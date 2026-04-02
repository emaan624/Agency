<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'referral_code' => \Illuminate\Support\Str::random(8),
        ]);

        $user->wallet()->create(['balance' => 0, 'currency' => 'USD']);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['success' => true, 'data' => ['user' => $user, 'token' => $token], 'message' => 'Registered successfully.'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        if ($user->is_banned) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Account suspended.'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['success' => true, 'data' => ['user' => $user, 'token' => $token], 'message' => 'Logged in.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'data' => null, 'message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user(), 'message' => 'OK']);
    }
}
