<?php

use App\Http\Controllers\API\AppDataController;
use App\Http\Controllers\API\Auth\APIAuthenticatedSessionController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\CallController;
use App\Http\Controllers\API\SmsController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\LogiPhone\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [APIAuthenticatedSessionController::class, 'store']);

Route::get('/company/all', [SupplierController::class, 'list']);
Route::get('/employees/all', [EmployeeController::class, 'list']);

Route::get('/favorite/all', [FavoriteController::class, 'list']);
Route::post('/favorite/add', [FavoriteController::class, 'save']);

Route::get('/appdata/all', [AppDataController::class, 'list']);
Route::get('/appdata/detail', [AppDataController::class, 'get']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [APIAuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});


// app user controller
Route::get('/app/getAllUsers', [SupplierController::class, 'getAllUsers']);

// app call controller
/*
param:
    {
        callNumber,receiveNumber
    }

res:{
    sender_id, receiver_id, created_at
}
*/
Route::post('/app/insertCall', [CallController::class, 'insertCall']);

/*
param:
    {
        callID,receiveID,date
    }

res:[]
*/
Route::post('/app/endCall', [CallController::class, 'endCall']);
// Route::post('/app/searchCall',[CallController::class,'searchCall']);


// sms controller

/*
param:
    {
        callNumber,receiveNumber
    }
*/

Route::post('/app/insertSms', [SmsController::class, 'insertSms']);

// Route::get('/app/searchSms',[SmsController::class,'searchSms']);

// server info
// makoto@tejima.jp
// test



//// ***** android app codes ********** ////

// Android get all user list
Route::get('/getAllUsersByPage', [EmployeeController::class, 'getAllUsersByPage']);
// get user details
Route::get('/getSpecificUser?keyword={keyword}', [EmployeeController::class, 'getSpecificUser']);



// Android Apis
/*
param:
    {
        email,password
    }
*/
Route::get('/login', [UserController::class, 'login']);

/*
param:
    {
        user_id, type
    }
*/
Route::get('/get-favorite-list', [FavoriteController::class, 'getAllFavoriteUsersBySpecificUser']);

/*
param:
    {
        no param
    }
*/
Route::get('/get-favorite-add-list', [FavoriteController::class, 'getFavoriteAddList']);

/*
param:
    {
        id,email,type
    }
*/
Route::get('/get-sms-list', [SmsController::class, 'getSMSList']);

/*
param:
    {
        user_id,type
    }
*/

Route::get('/get-profile', [UserController::class, 'getProfile']);

/*
param:
    {
        no param
    }
*/
Route::get('/get-logiphone-list', [UserController::class, 'getLogiphoneList']);

/*
param:
    {
        no param
    }
*/

Route::get('/get-logiscope-list', [UserController::class, 'getLogiscopeList']);

/*
param:
    {
        user_id, type
    }
*/
Route::get('/get-member-list', [UserController::class, 'getMemberList']);

/*
param:
    {
        user_id, type, selected_id
    }
*/

Route::get('/add-favorite-list', [FavoriteController::class, 'addFavoriteList']);

/*
param:
    {
        keyword(search-favorite-list), user_id
    }
*/

Route::get('/search-favorite-list', [FavoriteController::class, 'searchFavoriteList']);

/*
param:
    {
        keyword(search-favorite-add-list), user_id
    }
*/

Route::get('/search-favorite-add-list', [FavoriteController::class, 'searchFavoriteAddList']);

/*
param:
    {
        keyword(search-logiphone-list)
    }
*/

Route::get('/search-logiphone-list', [UserController::class, 'searchLogiphoneList']);

/*
param:
    {
        keyword(search-logiscope-list)
    }
*/

Route::get('/search-logiscope-list', [UserController::class, 'searchLogiscopeList']);

/*
param:
    {
        (add-employee)
    }
*/

Route::post('/add-employee', [UserController::class, 'addEmployee']);

/*
param:
    {
        (update-user)
    }
*/

Route::post('/update-user', [UserController::class, 'updateUser']);

/*
param:
    {
        user_id,selected_id, type(change-favorite-list)
    }
*/

Route::get('/change-favorite-list', [FavoriteController::class, 'changeFavoriteList']);

/*
param:
    {
        (get-all-companies) no param
    }
*/

Route::get('/get-all-companies', [CompanyController::class, 'getAllCompanies']);

/*
param:
    {
        (sender_id, receiver_id, user_type, content) no param
    }
*/

Route::get('/send-sms', [SmsController::class, 'sendSMS']);






// Logiscope-old Android code

Route::get('/get-oldlogi-list', [EmployeeController::class, 'getAllUsersByPageInOldPeople']);


/*
param:
    {
        (keyword) search users by keyword(name search)
    }
*/

Route::get('/search-old-user', [EmployeeController::class, 'searchOldUser']);


/*
param:
    {
        (keyword) search users by keyword(kana search)
    }
*/

Route::get('/search-user-by-kana', [EmployeeController::class, 'searchUserByKana']);

/*
param:
    {
        (company, people, individual) search users by keyword(kana search)
    }
*/

Route::get('/search-user-by-company', [EmployeeController::class, 'searchUserByCompany']);


/*
param:
    {
        keyword
    }
*/

Route::get('/search-company-by-name',[CompanyController::class, 'searchCompanyByName']);


/*
param:
    {
        no param
    }
*/

Route::get('/get-old-all-companies', [CompanyController::class, 'getOldAllCompanies']);

/*
param:
    {
        keyword
    }
*/

Route::get('/search-oldCompany-by-name', [CompanyController::class, 'searchOldCompanyByName']);

/*
param:
    {
        id, type
    }
*/

Route::get('/get-company-details', [CompanyController::class, 'getCompanyDetails']);