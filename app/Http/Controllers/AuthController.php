<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ApiToken;

class AuthController extends Controller
{
    public function signin(UserRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password.'], 401);
        }

        ApiToken::where('user_id', $user->id)->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        ApiToken::create([
            'user_id'   => $user->id,
            'token'     => $token,
            'is_active' => true,
            'expires_at'=> now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
        ]);
    }
}
