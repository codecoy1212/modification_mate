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
    #main
    Route::post('mobile/logout',[MobileController::class,'logout']);
    Route::get('mobile/profile/show',[MobileController::class,'profile']);
    Route::post('mobile/profile/update',[MobileController::class,'profile_updated']);
    Route::post('mobile/profile/update/password',[MobileController::class,'password_update']);

    #tasks
    Route::post('mobile/tasks/add',[MobileController::class,'add_task']);
    Route::get('mobile/tasks/show',[MobileController::class,'get_tasks']);
    Route::get('mobile/tasks/show/specific',[MobileController::class,'get_task_detail']);
    Route::get('mobile/users/show',[MobileController::class,'get_users']);
    Route::post('mobile/tasks/feedback',[MobileController::class,'give_feedback']);
    Route::get('mobile/tasks/sub_task',[MobileController::class,'get_sub_task_and_feedbacks']);
    Route::post('mobile/tasks/sub_task/rating',[MobileController::class,'give_rating']);
    Route::post('mobile/tasks/update',[MobileController::class,'update_task']);
    Route::get('mobile/tasks/history',[MobileController::class,'get_history']);
    Route::post('mobile/image/upload',[MobileController::class,'upload_file']);
    Route::post('mobile/tasks/delete_request',[MobileController::class,'delete_task']);
    Route::post('mobile/tasks/accept_request',[MobileController::class,'accept_task']);
});

#default
Route::get('default',function(){
    $str['status']=false;
    $str['message']="USER IS NOT AUTHENTICATED";
    return $str;
})->name('default');
Route::post('mobile/signup',[MobileController::class,'signup']);
Route::post('mobile/login',[MobileController::class,'login']);

