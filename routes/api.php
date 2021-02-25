<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'api\Auth\RegisterController@register');
Route::post('login', 'api\Auth\LoginController@login');

Route::post('password/forgot', 'api\ForgotPasswordController@forgot');
Route::post('password/reset', 'api\ForgotPasswordController@reset');

Route::middleware('auth:api')->group(function () {
    Route::apiResource('/tasks', 'api\TaskController');
    Route::get('/task/ongoing', 'api\TaskController@ongoingTasks');
    Route::get('/task/completed/', 'api\TaskController@completedTasks');
    Route::get('/task/tomorrow', 'api\TaskController@tomorrowTasks');
    Route::get('/task/status/{id}', 'api\TaskController@changeStatusTask');
    Route::get('/task/goTaskTomorrow/{id}', 'api\TaskController@goTaskTomorrow');
    Route::get('/task/backTaskToday/{id}', 'api\TaskController@backTaskToday');
});
