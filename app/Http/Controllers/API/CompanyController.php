<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Libs\Common\ApiClass;
use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
*	会社関連
*/
class CompanyController extends Controller
{
    /**
     * 会社の情報の取得
     * @return void
     */
    public function list(ApiClass $ApiClass)
    {
        // $user = Auth::user();

        // if ($user->company->company_base_id) {
        //     $Companies = Company::whereCompanyBaseId($user->company->company_base_id)->get();
        // } else {
        //     $Companies[] = $user->company;
        // }
        $Companies = Company::whereCompanyBaseId(1)->get();

        foreach ($Companies as $key => $Company) {
            if (!$Companies[$key]->is_company_branch) {
                //営業所がない場合は本社の住所を入れる
                $Companies[$key]->setMainOfficeInfo();
            }

            $Companies[$key]->children = CompanyBranch::with(["children.children"])
                                                      ->where("company_id", $Companies[$key]->id)
                                                      ->get();
        }

        return $ApiClass->responseOk(["response" => $Companies]);
    }

    /**
     * 社員一覧の取得
     */
    public function employeer_list(ApiClass $ApiClass)
    {
        $list = CompanyEmployee::with(["companyBranch.company","companyDepartment.children", "authority","roles"]);
            // ->whereHas('companyBranch.company', function ($query){
            //     $query->where('company_base_id', 1);
            // });

        $datas = $list->orderBy("id", "desc")->limit(300)->get();

        $response = $datas->map(function ($employeer) {
            return [
                'id' => $employeer->id,
                'person_name' => $employeer->person_name,
                'person_name_kana' => $employeer->person_name_kana,
                'person_nickname' => $employeer->nickname,
                'tel' => $employeer->tel1,
                'hire_date' => $employeer->hire_date,
                'email' => $employeer->email,
                'position' => $employeer->position,
                'gender' => $employeer->gender,
                'department' => $employeer->department,
                'is_retirement' => $employeer->is_retirement,
                'company_name_full_short' =>$employeer->companyBranch->company->company_name_full_short,
                'company_branch' =>([
                        'branch_name' => $employeer->companyBranch->branch_name,
                        'id' => $employeer->companyBranch->id,
                    ]),
                'roles'=>$this->getRolesFromEmployeeRole($employeer->roles)
            ];
        });

        return $ApiClass->responseOk(["response" => $response]);
    }

    function getRolesFromEmployeeRole($employeerRoles) {
        $roles = [];
        foreach ($employeerRoles as $role) {
            array_push($roles, $role->role);
        }
        return $roles;
    }

    // public function getAllCompanies()
    // {
    //     $result = Company::select('id', 'company_name')->paginate(20);
    //     return response()->json($result);
    // }

    public function getAllCompanies(Request $request)
    {
        $id = $request->id;
        $keyword = $request->keyword;
        $sort = $request->sort;
        $orderBy = $sort == 'normal' ? 'id' : 'branch_name';

        $user = CompanyEmployee::where('id', $id)->first();

        // $result = CompanyBranch::select('company_branches.id', 'company_branches.branch_name', 'company.branch_name  as company_name')
        //                         ->join('companies', 'company_branches.xid', '=', 'companies.xid') // Join company table
        //                         ->where('company_branches.branch_name', 'like', '%' . $keyword . '%') // Keep search filter
        //                         ->where($orderBy, '<>', '') // Ensure ordering column is not empty
        //                         ->orderBy($orderBy, 'asc') // Apply ordering
        //                         ->distinct()
        //                         ->paginate(30);

        $result = CompanyBranch::select('id', 'branch_name', 'nickname', 'zip', 'other', 'tel', 'fax')
                                ->where('branch_name', 'like', '%' . $keyword . '%')
                                ->where('company_id', $user->company_id)
                                ->where($orderBy, '<>', '')
                                ->orderBy($orderBy, 'asc')
                                ->distinct()
                                ->paginate(30);

        $result->getCollection()->transform(function ($data) {
            $data->type = 0;
            return $data;
        });

        return response()->json($result);
    }

    public function getOldAllCompanies(Request $request)
    {
        $keyword = $request->keyword;
        $sort = $request->sort;
        $orderBy = $sort == 'normal' ? 'id' : 'kana';

        $subQuery = DB::connection('mysql_old')
                      ->table('clientcompany')
                      ->selectRaw('MIN(id) as id')
                      ->where('company_name', 'like', '%' . $keyword . '%')
                      ->where($orderBy, '<>', '')
                      ->orderBy($orderBy, 'asc')
                      ->groupBy('company_name', 'kana', 'url');

        $result = DB::connection('mysql_old')
                    ->table('clientcompany as c')
                    ->joinSub($subQuery, 'min_ids', function ($join) {
                        $join->on('c.id', '=', 'min_ids.id');
                    })
                    ->select('c.id', 'c.company_name', 'c.kana', 'c.url')
                    ->paginate(30);

        $result->getCollection()->transform(function ($data) {
            $data->type = 1;
            return $data;
        });

        return response()->json($result);
    }

    public function searchCompanyByName(Request $request)
    {
        $keyword = $request->keyword;
        $result = DB::connection('mysql')->table('companies')->where('company_name', "like", "%" . $keyword . "%")->paginate(50);

        return response()->json($result);
    }

    public function searchOldCompanyByName(Request $request)
    {
        $keyword = $request->keyword;
        $result = DB::connection('mysql_old')->table('clientcompany')->where('company_name', "like", "%" . $keyword . "%")->paginate(50);

        return response()->json($result);
    }

    public function getCompanyDetails(Request $request)
    {
        $type = $request->type;
        $result = [];

        if($type == 1) {
            $result = DB::connection('mysql_old')
                        ->table('clientcompany')
                        ->join('client', 'clientcompany.xid', '=', 'client.xid') // Join client table
                        ->select('clientcompany.*') // Selecting necessary columns
                        ->where('clientcompany.xid', $request->id)
                        ->get();

            // $result = DB::connection('mysql_old')
            //             ->table('clientcompany')
            //             ->select('id', 'company_name', 'kana as company_name_kana', 'url')
            //             ->where('id', $request->id)
            //             ->distinct()
            //             ->get();
        } else {
            $result = DB::connection('mysql')
                        ->table('company_branches')
                        ->select('id', 'branch_name', 'nickname', 'zip', 'other', 'tel', 'fax')
                        ->where('id', $request->id)
                        ->get();

            // $result = DB::connection('mysql')
            //             ->table('companies')
            //             ->select('id', 'company_name', 'company_name_kana', 'url')
            //             ->where('id', $request->id)
            //             ->distinct()
            //             ->get();
        }

        if($result) return response()->json($result[0]);
        else return response()->json($result);
    }
}
