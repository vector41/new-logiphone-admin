<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPEmployee;
use App\Models\LogiPhone\LPUser;
use App\Models\LogiPhone\LPPhone;
use App\Models\LogiPhone\LPFavorite;
use App\Models\LogiScope\CompanyBranch;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\User;
use App\Models\LogiScopeOld\Staff;

class UserController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();
            // $token = $user->createToken($request->email)->plainTextToken;
            config(['app.userCompanyID'=> $user->company_id]);
            return response()->json(['userId' => $user->id, 'email' => $user->email], 200);
        } else {
            return response()->json(['userId' => 'invalid'], 200);
        }
        // else {
        //     $user = LPCompanyEmployee::where('email', $request->email)->first();

        //     if ($user && $request->password == $user->password) {
        //         $token = $user->createToken('api-token')->plainTextToken;
                
        //         config(['app.userCompanyID'=> $user->company_id]);
        //         return response()->json(['token' => $token, 'userId' => $user->id, 'email' => $user->mail], 200);
        //     }
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        // $user = LPCompanyEmployee::where('email', $request->email)->first();

        // if ($user && $request->password == $user->password) {
        //     return response()->json(['userId' => $user->id, 'email' => $user->email], 200);
        // } else {
        //     $user = LPEmployee::where('email', $request->email)->first();
        //     if ($user && $request->password == $user->password) {
        //         config(['app.userCompanyID'=> $user->company_id]);
        //         return response()->json(['userId' => $user->id, 'email' => $user->email], 200);
        //     }
        // }
        // return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function getProfile(Request $request)
    {
        $type = $request->type;
        $myId = $request->id;
        $otherId = $request->user_id;

        if ($type == 0) {
            $user = CompanyEmployee::where('id', $otherId)->get(['person_name_first', 'person_name_second', 'person_name_first_kana', 'person_name_second_kana', 'tel1', 'tel2', 'tel3', 'email', 'position', 'birth_date', 'blood', 'gender', 'zip', 'city', 'prefecture', 'company_id', 'department']);

            if (!$user->isEmpty()) {
                $companyName = Company::where('id', $user[0]->company_id)->get(['company_name']);

                $user = $user->map(function ($item) use ($companyName) {
                    $item->company_name = $companyName;
                    $item->role_screen = '';
                    return $item;
                });
            }

            $status = LPFavorite::where('user_id', $myId)
                                ->where('selected_id', $otherId)
                                ->get();

            if (!$status->isEmpty()) {
                return response()->json(['profile' => $user[0], 'favorite' => 1]);
            } else return response()->json(['profile' => $user[0], 'favorite' => 0]);
        } else {
            $user = Staff::where('id', $otherId)->get(['person_name_first', 'person_name_second', 'person_name_first_kana', 'person_name_second_kana', 'tel1', 'tel2', 'tel3', 'email', 'position', 'birth_date', 'blood', 'gender', 'zip', 'city', 'prefecture', 'company_id', 'department']);

            if (!$user->isEmpty()) {
                $companyName = Company::where('id', $user[0]->company_id)->get(['company_name']);

                $user = $user->map(function ($item) use ($companyName) {
                    $item->company_name = $companyName;
                    $item->role_screen = '';
                    return $item;
                });
            }

            $status = LPFavorite::where('user_id', $myId)
                                ->where('selected_id', $otherId)
                                ->get();

            if (!$status->isEmpty()) {
                return response()->json(['profile' => $user[0], 'favorite' => 1]);
            } else return response()->json(['profile' => $user[0], 'favorite' => 0]);
        }
    }

    public function getLogiphoneList(Request $request)
    {
        $keyword = $request->keyword;
        $sort = $request->sort;
        $orderBy = $sort == 'normal' ? 'id' : 'person_name_second_kana';

        $users = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'tel1', 'tel2', 'tel3', 'gender')
                                ->where(function ($query) use ($keyword) {
                                    $query->where('person_name_second', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_first', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_second_kana', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_first_kana', 'like', '%' . $keyword . '%'); })
                                ->where($orderBy, '<>', '')
                                ->orderBy($orderBy, 'asc')
                                ->paginate(30);

        $users->getCollection()->transform(function ($user) {
            $user->type = 0;
            return $user;
        });

        return response()->json($users);
        // $keyword = $request->keyword;
        // $users = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')
        //                     ->where(function ($query) use ($keyword) {
        //                         $query->where('person_name_second', 'like', '%' . $keyword . '%')
        //                               ->orWhere('person_name_first', 'like', '%' . $keyword . '%'); })
        //                     ->paginate(30);
        // $users->getCollection()->transform(function ($user) {
        //     $user->type = 1;
        //     return $user;
        // });
        // return response()->json($users);
    }

    public function getLogiscopeList(Request $request)
    {
        $id = $request->id;
        $keyword = $request->keyword;
        $sort = $request->sort;
        $orderBy = $sort == 'normal' ? 'id' : 'person_name_second_kana';

        $user = CompanyEmployee::where('id', $id)->first();

        $users = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'tel1', 'tel2', 'tel3', 'gender')
                                ->where(function ($query) use ($keyword) {
                                    $query->where('person_name_second', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_first', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_second_kana', 'like', '%' . $keyword . '%')
                                          ->orWhere('person_name_first_kana', 'like', '%' . $keyword . '%'); })
                                ->where($orderBy, '<>', '')
                                ->where('company_id', $user->company_id)
                                ->orderBy($orderBy, 'asc')
                                ->paginate(30);

        $users->getCollection()->transform(function ($user) {
            $user->type = 0;
            return $user;
        });

        return response()->json($users);
    }

    public function getMemberList(Request $request)
    {
        $branchId = $request->company_id;
        $type = $request->type;

        if ($type == 0) {
            $users = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'gender')
                                    ->where('company_branch_id', $branchId)
                                    ->where(function ($query) {
                                        $query->where('email', '')
                                              ->orWhere('password', '');
                                    })
                                    ->paginate(30);

            $users->getCollection()->transform(function ($user) {
                $user->gender = $user->gender == 'null' ? 1 : $user->gender;
                $user->type = 0;
                return $user;
            });

            return response()->json($users);
        } else {
            $users = Staff::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'gender')
                          ->where('clientCompany', $branchId)
                          ->where(function ($query) {
                            $query->where('email', '')
                                  ->orWhere('password', '');
                          })
                          ->paginate(30);

            $users->getCollection()->transform(function ($user) {
                $user->gender = $user->gender == 'null' ? 1 : $user->gender;
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
        $result = LPEmployee::where('person_name_first', 'like', '%' . $keyword . '%')
                            ->orWhere('person_name_second', 'like', '%' . $keyword . '%')
                            ->paginate(30);

        return response()->json($result);
    }

    public function searchLogiscopeList(Request $request)
    {
        $keyword = $request->keyword;
        $result = CompanyEmployee::where('person_name_first', 'like', '%' . $keyword . '%')
                                ->orWhere('person_name_second', 'like', '%' . $keyword . '%')
                                ->paginate(30);

        return response()->json($result);
    }

    public function addEmployee(Request $request)
    {
        $userId = $request->user_id;
        $user = CompanyEmployee::where('id', $userId)->first();

        // $person_name_first = $request->person_name_first;
        // $person_name_second = $request->person_name_second;
        // $person_name_first_kana = $request->person_name_first_kana;
        // $person_name_second_kana = $request->person_name_second_kana;
        // $nickname = $request->nickname;
        // $position = $request->role;
        // $birth_date = $request->birth_date;
        // $gender = $request->gender;
        // $prefecture = $request->prefecture;
        // $city = $request->city;
        // $blood = $request->blood;
        // $zip = $request->zip;
        // $tel1 = $request->tel1;
        // $tel2 = $request->tel2;
        // $tel3 = $request->tel3;
        // $roleScreen = $request->role_screen;


        $employee = new CompanyEmployee();
        $employee->person_name_first = $request->person_name_first;
        $employee->person_name_second = $request->person_name_second;
        $employee->person_name_first_kana = $request->person_name_first_kana;
        $employee->person_name_second_kana = $request->person_name_second_kana;
        $employee->nickname = $request->nickname;
        $employee->blood = $request->blood;
        $employee->position = $request->position;
        $employee->birth_date = $request->birth_date;
        $employee->gender = $request->gender;
        $employee->prefecture = $request->prefecture;
        $employee->city = $request->city;
        $employee->zip = $request->zip;
        $employee->tel1 = $request->tel1;
        $employee->tel2 = $request->tel2;
        $employee->tel3 = $request->tel3;
        $employee->company_id = $user->company_id;
        $employee->role_screen = $request->role_screen;

        $employee->save();

        // if($company == null)
        // {
        //     $department = $request->input('department');

        //     $employee = new LPEmployee();
        //     $employee->person_name_first = $person_name_first;
        //     $employee->person_name_second = $person_name_second;
        //     $employee->person_name_first_kana = $person_name_first_kana;
        //     $employee->person_name_second_kana = $person_name_second_kana;
        //     $employee->nickname = $nickname;
        //     $employee->email = $email;
        //     $employee->password = $password;
        //     $employee->blood = $blood;
        //     $employee->position = $position;
        //     $employee->birth_date = $birth_date;
        //     $employee->gender = $gender;
        //     $employee->prefecture = $prefecture;
        //     $employee->city = $city;
        //     $employee->zip = $zip;

        //     $employee->tel1 = $tel1;
        //     $employee->tel2 = $tel2;
        //     $employee->tel3 = $tel3;

        //     $employee->company_id = 0;
        //     $employee->company_name = $companyName;
        //     $employee->department = $department;
        //     $employee->role_screen = $roleScreen;

        //     $employee->save();
        // }
        // else {
        //     $companyId = $company->id;
        //     $department = $request->input('department');

        //     $employee = new LPEmployee();
        //     $employee->person_name_first = $person_name_first;
        //     $employee->person_name_second = $person_name_second;
        //     $employee->person_name_first_kana = $person_name_first_kana;
        //     $employee->person_name_second_kana = $person_name_second_kana;
        //     $employee->nickname = $nickname;
        //     $employee->email = $email;
        //     $employee->password = $password;
        //     $employee->blood = $blood;
        //     $employee->position = $position;
        //     $employee->birth_date = $birth_date;
        //     $employee->gender = $gender;
        //     $employee->prefecture = $prefecture;
        //     $employee->city = $city;
        //     $employee->zip = $zip;

        //     $employee->tel1 = $tel1;
        //     $employee->tel2 = $tel2;
        //     $employee->tel3 = $tel3;

        //     $employee->company_id = $companyId;
        //     $employee->company_name = $companyName;
        //     $employee->department = $department;
        //     $employee->role_screen = $roleScreen;

        //     $employee->save();

    // }
        return response()->json(['message' => 'success'], 200);
    }

    public function updateUser(Request $request)
    {
        $userId = $request->user_id;
        $type = $request->type;

        if ($type == 0) {
            $person_name_first = $request->input('person_name_first');
            $person_name_second = $request->input('person_name_second');
            $person_name_first_kana = $request->input('person_name_first_kana');
            $person_name_second_kana = $request->input('person_name_second_kana');
            $nickname = $request->input('nickname');
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

            $user = CompanyEmployee::where('id', $userId)->first();

            $user->person_name_first = $person_name_first;
            $user->person_name_second = $person_name_second;
            $user->person_name_first_kana = $person_name_first_kana;
            $user->person_name_second_kana = $person_name_second_kana;
            $user->nickname = $nickname;
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
            $user->role_screen = $roleScreen;

            $user->save();

            return response()->json(['message' => 'success'], 200);
        }
    }
}
