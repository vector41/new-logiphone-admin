<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogiPhone\LPCompanyEmployee;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\LogiPhone\LPUser;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\User;

class UserController extends Controller
{
    //
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Success
            $authUser = auth()->user();
            $token = $authUser->createToken($request->email)->plainTextToken;
            return response()->json(['token' => $token, 'userId' => $authUser->id, 'email' => $authUser->mail], 200);
        } else {
            // Failure
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function getProfile(Request $request)
    {
        $type = $request->type;
        $userId = $request->id;
        if ($type == 0) {
            $user = CompanyEmployee::where('id', $userId)->first();
            if (!$user)
                $user = User::where('id', $userId)->first();
            return response()->json($user);
        } else {
            $user = LPCompanyEmployee::where('id', $userId)->first();
            if (!$user)
                $user = User::where('id', $userId)->first();
            return response()->json($user);
        }
    }

    public function getLogiphoneList(Request $request)
    {
        $users = LPCompanyEmployee::paginate(50);
        return response()->json($users);
    }

    public function getLogiscopeList(Request $request)
    {
        $users = CompanyEmployee::paginate(50);
        return response()->json($users);
    }
}
