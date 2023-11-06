<?php

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


Route::any('/bot',[\App\Http\Controllers\BotController::class,'index']);
Route::any('/bot/diabetes',[\App\Http\Controllers\BotController::class,'index']);


Route::any('/dialogflow/payload',[\App\Http\Controllers\DialogFlowController::class,'index']);
Route::any('/dialogflow/test',[\App\Http\Controllers\DialogFlowController::class,'init_dialogFlow_two']);
