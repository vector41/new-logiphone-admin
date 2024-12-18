<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\LogiPhone\LPUser;

class UserController extends Controller
{
    //
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Success
            $authUser = auth()->user();
            $token = $authUser->createToken($request->email)->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            // Failure
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }
}
