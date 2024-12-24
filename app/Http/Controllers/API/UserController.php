<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPEmployee;
use App\Models\LogiPhone\LPUser;
use App\Models\LogiPhone\LPPhone;
use App\Models\LogiPhone\LPFavorite;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\User;

class UserController extends Controller
{
    //
    public function login(Request $request)
    {
        // if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        //     $authUser = auth()->user();
        //     $token = $authUser->createToken($request->email)->plainTextToken;
        //     return response()->json(['token' => $token, 'userId' => $authUser->id, 'email' => $authUser->mail], 200);
        // } else {
        //     $user = LPCompanyEmployee::where('email', $request->email)->first();

        //     if ($user && $request->password == $user->password) {
        //         $token = $user->createToken('api-token')->plainTextToken;
        //         return response()->json(['token' => $token, 'userId' => $user->id, 'email' => $user->mail], 200);
        //     }
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        $user = LPCompanyEmployee::where('email', $request->email)->first();

        if ($user && $request->password == $user->password) {
            return response()->json(['userId' => $user->id, 'email' => $user->email], 200);
        } else {
            $user = LPEmployee::where('email', $request->email)->first();
            if ($user && $request->password == $user->password) {
                return response()->json(['userId' => $user->id, 'email' => $user->email], 200);
            }
        }
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function getProfile(Request $request)
    {
        $type = $request->type;
        $myId = $request->id;
        $otherId = $request->user_id;

        if ($type == 0) {
            $user = CompanyEmployee::where('id', $otherId)->get(['person_name_first', 'person_name_second', 'person_name_first_kana', 'person_name_second_kana', 'tel1', 'tel2', 'tel3', 'nickname', 'email', 'position', 'birth_date', 'blood', 'gender', 'zip', 'city', 'prefecture', 'company_id', 'department']);

            if (!$user->isEmpty()) {
                $companyName = Company::where('id', $user[0]->company_id)->get(['company_name']);

                $user = $user->map(function ($item) use ($companyName) {
                    $item->company_name = $companyName;
                    $item->role_screen = "";
                    return $item;
                });
            }

            $status = LPFavorite::where('user_id', $myId)
                                ->where('selected_id', $otherId)
                                ->get();

            if (!$status->isEmpty()) {
                return response()->json(["profile" => $user[0], 'favorite' => 1]);
            } else return response()->json(["profile" => $user[0], 'favorite' => 0]);
        } else {
            $user = LPEmployee::where('id', $otherId)->get(['person_name_first', 'person_name_second', 'person_name_first_kana', 'person_name_second_kana', 'nickname', 'tel1', 'tel2', 'tel3', 'email', 'position', 'role_screen', 'birth_date', 'blood', 'gender', 'zip', 'city', 'prefecture', 'company_id', 'department']);

            if (!$user->isEmpty()) {
                $companyName = Company::where('id', $user[0]->company_id)->get(['company_name']);

                $user = $user->map(function ($item) use ($companyName) {
                    $item->company_name = $companyName;
                    return $item;
                });
            }

            $status = LPFavorite::where('user_id', $myId)
                                ->where('selected_id', $otherId)
                                ->get();

            if (!$status->isEmpty()) {
                return response()->json(["profile" => $user[0], 'favorite' => 1]);
            } else return response()->json(["profile" => $user[0], 'favorite' => 0]);
        }
    }

    public function getLogiphoneList(Request $request)
    {
        $keyword = $request->keyword;
        $users = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')
                            ->where(function ($query) use ($keyword) {
                                $query->where('person_name_second', 'like', '%' . $keyword . '%')
                                      ->orWhere('person_name_first', 'like', '%' . $keyword . '%');
                            })
                            ->paginate(30);
        $users->getCollection()->transform(function ($user) {
            $user->type = 1;
            return $user;
        });
        return response()->json($users);
    }

    public function getLogiscopeList(Request $request)
    {
        $keyword = $request->keyword;
        $users = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')
                                ->where(function ($query) use ($keyword) {
                                    $query->where('person_name_second', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_first', 'like', '%' . $keyword . '%');
                                })->paginate(30);
        $users->getCollection()->transform(function ($user) {
            $user->type = 0;
            return $user;
        });
        return response()->json($users);
    }

    public function getMemberList(Request $request)
    {
        $userId = $request->user_id;
        $type = $request->type;

        if ($type == 0) {
            $user = CompanyEmployee::where('id', $userId)->first();
            $users = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')
                                    ->where('company_id', $user->company_id)
                                    ->paginate(30);
            $users->getCollection()->transform(function ($user) {
                $user->type = 0;
                return $user;
            });

            return response()->json($users);
        } else {
            $user = LPEmployee::where('id', $userId)->first();
            $users = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')
                                ->where('company_id', $user->company_id)
                                ->paginate(30);
            $users->getCollection()->transform(function ($user) {
                $user->type = 1;
                return $user;
            });

            return response()->json($users);
        }
    }

    // search methond
    public function searchLogiphoneList(Request $request)
    {
        $keyword = $request->keyword;
        $result = LPEmployee::where('person_name_first', "like", "%" . $keyword . "%")
            ->orWhere('person_name_second', "like", "%" . $keyword . "%")
            ->paginate(30);

        return response()->json($result);
    }

    public function searchLogiscopeList(Request $request)
    {
        $keyword = $request->keyword;
        $result = CompanyEmployee::where('person_name_first', "like", "%" . $keyword . "%")
            ->orWhere('person_name_second', "like", "%" . $keyword . "%")
            ->paginate(30);

        return response()->json($result);
    }

    public function addEmployee(Request $request)
    {
        $person_name_first = $request->input('person_name_first');
        $person_name_second = $request->input('person_name_second');
        $person_name_first_kana = $request->input('person_name_first_kana');
        $person_name_second_kana = $request->input('person_name_second_kana');

        $nickname = $request->input('nickname');
        $email = $request->input('email');
        $password = $request->input('password');
        $position = $request->input('role');
        $birth_date = $request->input('birth_date');
        $gender = $request->input('gender');
        $prefecture = $request->input('prefecture');
        $city = $request->input('city');
        $blood = $request->input('blood');
        $zip = $request->input('zip');

        $tel1 = $request->input('tel1');
        $tel2 = $request->input('tel2');
        $tel3 = $request->input('tel3');

        $roleScreen = $request->input('role_screen');
        $companyName = $request->input('company_name');

        $company = Company::where('company_name', 'like', "%{$companyName}%")->first();
        $companyId = $company->id;
        $department = $request->input('department');

        $employee = new LPEmployee();
        $employee->person_name_first = $person_name_first;
        $employee->person_name_second = $person_name_second;
        $employee->person_name_first_kana = $person_name_first_kana;
        $employee->person_name_second_kana = $person_name_second_kana;
        $employee->nickname = $nickname;
        $employee->email = $email;
        $employee->password = $password;
        $employee->blood = $blood;
        $employee->position = $position;
        $employee->birth_date = $birth_date;
        $employee->gender = $gender;
        $employee->prefecture = $prefecture;
        $employee->city = $city;
        $employee->zip = $zip;

        $employee->tel1 = $tel1;
        $employee->tel2 = $tel2;
        $employee->tel3 = $tel3;

        $employee->company_id = $companyId;
        $employee->company_name = $companyName;
        $employee->department = $department;
        $employee->role_screen = $roleScreen;

        $employee->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function updateUser(Request $request)
    {
        return response()->json(['message' => $request->all()], 200);
        $userId = $request->user_id;
        $type = $request->type;

        if ($type == 1) {
            $person_name_first = $request->input('person_name_first');
            $person_name_second = $request->input('person_name_second');
            $person_name_first_kana = $request->input('person_name_first_kana');
            $person_name_second_kana = $request->input('person_name_second_kana');
            $nickname = $request->input('nickname');
            $email = $request->input('email');
            $position = $request->input('role');
            $birth_date = $request->input('birth_date');
            $gender = $request->input('gender');
            $roleScreen = $request->input('role_screen');

            $prefecture = $request->input('prefecture');
            $city = $request->input('city');

            $tel1 = $request->input('tel1');
            $tel2 = $request->input('tel2');
            $tel3 = $request->input('tel3');

            $blood = $request->input('blood');
            $zip = $request->input('zip');

            $companyName = $request->input('company_name');
            $company = Company::where('company_name', 'like', "%{$companyName}%")->first();
            $companyId = $company->id;

            $user = LPEmployee::where('id', $userId)->first();

            $user->person_name_first = $person_name_first;
            $user->person_name_second = $person_name_second;
            $user->person_name_first_kana = $person_name_first_kana;
            $user->person_name_second_kana = $person_name_second_kana;
            $user->nickname = $nickname;
            $user->email = $email;
            $user->position = $position;
            $user->birth_date = $birth_date;
            $user->gender = $gender;
            $user->blood = $blood;
            $user->zip = $zip;
            $user->tel1 = $tel1;
            $user->tel2 = $tel2;
            $user->tel3 = $tel3;
            $user->city = $city;
            $user->prefecture = $prefecture;
            $user->company_id = $companyId;
            $user->role_screen = $roleScreen;
            $user->company_name = $companyName;

            $user->save();

            return response()->json(['message' => 'success'], 200);
        }
    }
}
