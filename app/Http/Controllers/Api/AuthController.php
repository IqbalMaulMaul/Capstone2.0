<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PushToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif.'],
            ]);
        }

        // Generate token
        $deviceName = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                ]
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Remove push tokens for this user's current device
        if ($request->has('push_token')) {
            PushToken::where('token', $request->push_token)->delete();
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Store or update Expo push token for the authenticated user.
     */
    public function storePushToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        PushToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'device_name' => $request->device_name ?? 'unknown',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Push token berhasil disimpan',
        ]);
    }

    /**
     * Remove a specific push token (e.g., on logout).
     */
    public function deletePushToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        PushToken::where('token', $request->token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Push token berhasil dihapus',
        ]);
    }

    /**
     * Get current authenticated user info.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    }
}
