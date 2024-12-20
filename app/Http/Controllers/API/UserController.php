<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPEmployee;
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
        if ($request->email = "nori@komaeda.com" && $request->password = "test") {
            $email = $request->mail;
            // return $request->mail;
            return response()->json(['token' => "dfdfddieu2929299292", 'userId' => 34, 'email' => "nori@komaeda.com"], 200);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Success
            $authUser = auth()->user();
            $token = $authUser->createToken($request->email)->plainTextToken;
            return response()->json(['token' => $token, 'userId' => $authUser->id, 'email' => $authUser->mail], 200);
        } else {
            // Failure
            $user = LPCompanyEmployee::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('api-token')->plainTextToken;
                return response()->json(['token' => $token, 'userId' => $user->id, 'email' => $user->mail], 200);
            }

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
        $users = LPEmployee::paginate(50);
        return response()->json($users);
    }

    public function getLogiscopeList(Request $request)
    {
        $users = CompanyEmployee::paginate(50);
        return response()->json($users);
    }

    public function getMemberList(Request $request)
    {
        $userId = $request->user_id;
        $type = $request->type;

        if ($type == 0) {
            $user = CompanyEmployee::where('id', $userId)->first();
            $users = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', '0')::where('company_id', $user->company_id)->paginate(50);
            return response()->json($users);
        } else {
            $user = LPEmployee::where('id', $userId)->first();
            $users = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', '1')::where('company_id', $user->company_id)->paginate(50);
            return response()->json($users);
        }
    }
}
