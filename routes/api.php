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
    Route::post('/task/ongoing', 'api\TaskController@ongoingTasks');
    Route::post('/task/completed/', 'api\TaskController@completedTasks');
    Route::post('/task/tomorrow', 'api\TaskController@tomorrowTasks');
    Route::post('/task/status/{id}', 'api\TaskController@changeStatusTask');
    Route::post('/task/goTaskTomorrow/{id}', 'api\TaskController@goTaskTomorrow');
    Route::post('/task/backTaskToday/{id}', 'api\TaskController@backTaskToday');
});
