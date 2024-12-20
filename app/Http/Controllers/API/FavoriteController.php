<?php

namespace App\Http\Controllers\API;

use App\Libs\Common\ApiClass;
use App\Models\LogiPhone\Favorite;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPEmployee;
use App\Models\LogiPhone\LPFavorite;
use App\Models\LogiPhone\LPUser;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{

    /**
     * Update the user's profile information.
     */
    public function list(ApiClass $ApiClass, Request $request)
    {
        try {

            $favorites = Favorite::where('employeer', $request->employeer)->orderBy('id')->get();
            $favorite_datas = $favorites->map(function ($fav) {
                $favorite_detail = null;
                if ($fav->main_cat == 1 && $fav->emp_pos == 1) {
                    $favorite_detail = LPCompanyBranch::with('company')->find($fav->fovoriter);
                } else if ($fav->main_cat == 1 && $fav->emp_pos == 2) {
                    $favorite_detail = CompanyBranch::with('company')->find($fav->fovoriter);
                } else if ($fav->main_cat == 2 && $fav->emp_pos == 1) {
                    $favorite_detail = LPCompanyEmployee::find($fav->fovoriter);
                } else {
                    $favorite_detail = CompanyEmployee::find($fav->fovoriter);
                }
                return [
                    'id' => $favorite_detail->id,
                    'store_pos' => $fav->fav_pos,
                    'main_cat' => $fav->main_cat,
                    'name' => $fav->main_cat == 1 ? $favorite_detail->company->company_name_full_short : $favorite_detail->person_name_second . $favorite_detail->person_name_first,
                    'tel' => $fav->main_cat == 1 ? $favorite_detail->tel : $favorite_detail->tel1,
                ];
            });

            return response()->json($favorite_datas);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    /**
     * Update the user's profile information.
     */
    public function save(ApiClass $ApiClass, Request $request)
    {
        try {

            $favorite = Favorite::updateOrCreate([
                'emp_pos' => $request->emp_pos,
                'employeer' => $request->employeer,
                'fav_pos' => $request->fav_pos,
                'main_cat' => $request->main_cat,
                'fovoriter' => $request->fovoriter,
            ]);

            $favorites = Favorite::where('employeer', $request->employeer)->orderBy('id')->get();
            $favorite_datas = $favorites->map(function ($fav) {
                $favorite_detail = null;
                if ($fav->main_cat == 1 && $fav->emp_pos == 1) {
                    $favorite_detail = LPCompanyBranch::with('company')->find($fav->fovoriter);
                } else if ($fav->main_cat == 1 && $fav->emp_pos == 2) {
                    $favorite_detail = CompanyBranch::with('company')->find($fav->fovoriter);
                } else if ($fav->main_cat == 2 && $fav->emp_pos == 1) {
                    $favorite_detail = LPCompanyEmployee::find($fav->fovoriter);
                } else {
                    $favorite_detail = CompanyEmployee::find($fav->fovoriter);
                }
                return [
                    'id' => $favorite_detail->id,
                    'store_pos' => $fav->fav_pos,
                    'main_cat' => $fav->main_cat,
                    'name' => $fav->main_cat == 1 ? $favorite_detail->company->company_name_full_short : $favorite_detail->person_name_second . $favorite_detail->person_name_first,
                    'tel' => $fav->main_cat == 1 ? $favorite_detail->tel : $favorite_detail->tel1,
                ];
            });

            return response()->json($favorite_datas);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }


    /**
     * add favorite list.
     */

    public function addFavoriteList(Request $request)
    {
        $userId = $request->user_id;
        $type = $request->type;
        $selected_id = $request->selected_id;

        if ($type == 0) {
            $user = CompanyEmployee::where('id', $selected_id)->first();
            if ($user) {
                $favorite = new LPFavorite();
                $favorite->user_id = $userId;
                $favorite->selected_id = $selected_id;
                $favorite->type = $type;
                $favorite->first_name  = $user->person_name_first;
                $favorite->second_name  = $user->person_name_second;
                $favorite->first_name_kana = $user->person_name_first_kana;
                $favorite->second_name_kana = $user->person_name_second_kana;
                if ($user->gender != null)
                    $favorite->gender = $user->gender;
                $favorite->save();

                return response()->json(['message' => 'success'], 200);
            }
        } else {
            $user = LPEmployee::where('id', $selected_id)->first();
            if ($user) {
                $favorite = new LPFavorite();
                $favorite->user_id = $userId;
                $favorite->type = $type;
                $favorite->selected_id = $selected_id;
                $favorite->first_name  = $user->person_name_first;
                $favorite->second_name  = $user->person_name_second;
                $favorite->first_name_kana = $user->person_name_first_kana;
                $favorite->second_name_kana = $user->person_name_second_kana;
                if ($user->gender != null)
                    $favorite->gender = $user->gender;
                $favorite->save();

                return response()->json(['message' => 'success'], 200);
            }
        }
    }

    /**
     * get all favorite user list.
     */
    public function getAllFavoriteUsersBySpecificUser(Request $request)
    {

        $userId = $request->user_id;
        $allFavoriteList = LPFavorite::where('user_id', $userId)->paginate(50);
        return response()->json($allFavoriteList);
    }

    public function getFavoriteAddList()
    {

        $page = request()->get('page', 1);
        $pageSize = 50; // Records per page

        $logiphoneEmployees = DB::connection('mysql_lp')->table('employees')
            ->select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender', DB::raw("'Logiphone' as source"));

        $logiscopeEmployees = DB::table('company_employees')
            ->select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender', DB::raw("'Logiscope' as source"));

        $employees = $logiphoneEmployees
            ->unionAll($logiscopeEmployees)
            ->orderBy('person_name_first')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return response()->json($employees);

        // $scopeEmployees = CompanyEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')->orderBy('person_name_first')->paginate(25);
        // $phoneUsers = LPEmployee::select('id', 'person_name_second', 'person_name_first', 'person_name_second_kana', 'person_name_first_kana', 'nickname', 'gender')->orderBy('person_name_first')->paginate(25);

        // return response()->json(["Logiscope" => $scopeEmployees, "LogiPhone" => $phoneUsers]);

    }

    public function searchFavoriteList(Request $request)
    {
        $keyword = $request->keyword;
        $userId = $request->user_id;
        $result = LPFavorite::where("first_name", "like", "%" . $keyword . "%")
            ->orWhere("second_name", "like", "%" . $keyword . "%")
            ->orWhere("user_id", "=", $userId)
            ->paginate(50);

        return response()->json($result);
    }

    public function searchFavoriteAddList(Request $request)
    {
        $keyword = $request->keyword;
        $results = DB::table('Logiphone.favorites AS f')
            ->leftJoin('Logiphone.employees AS e', function ($join) {
                $join->on('f.type', '=', DB::raw(1))
                    ->on('f.foreign_key', '=', 'e.id');
            })
            ->leftJoin('Logiscope.company_employees AS ce', function ($join) {
                $join->on('f.type', '=', DB::raw(0))
                    ->on('f.foreign_key', '=', 'ce.id');
            })
            ->select(
                'f.id AS favorite_id',
                'f.type',
                DB::raw("
            CASE
                WHEN f.type = 1 THEN e.person_name_first
                WHEN f.type = 0 THEN ce.person_name_first
                ELSE NULL
            END AS name
        "),
                DB::raw("
            CASE
                WHEN f.type = 1 THEN e.person_name_second
                WHEN f.type = 0 THEN ce.person_name_second
                ELSE NULL
            END AS email
        ")
            )
            ->where(function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('f.type', '=', 1)
                        ->where('e.name', 'LIKE', "%{$keyword}%");
                })
                    ->orWhere(function ($subQuery) use ($keyword) {
                        $subQuery->where('f.type', '=', 0)
                            ->where('ce.name', 'LIKE', "%{$keyword}%");
                    });
            })
            ->paginate(50);

        return response()->json($results);
    }
}
