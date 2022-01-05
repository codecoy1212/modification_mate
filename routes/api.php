<?php

use App\Http\Controllers\MobileController;
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

Route::group(['middleware' => 'auth:sanctum'], function(){
    //All secure URL's
    Route::post('mobile/logout',[MobileController::class,'logout']);
    Route::get('mobile/profile/show',[MobileController::class,'profile']);
    Route::post('mobile/profile/update',[MobileController::class,'profile_updated']);
    Route::post('mobile/profile/update/password',[MobileController::class,'password_update']);
});

Route::get('default',function(){
    $str['status']=false;
    $str['message']="USER IS NOT AUTHENTICATED";
    return $str;
})->name('default');

Route::post('mobile/signup',[MobileController::class,'signup']);
Route::post('mobile/login',[MobileController::class,'login']);

