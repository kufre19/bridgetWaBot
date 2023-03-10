<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    
    return view('questions_and_answers.add');
});

Route::any("test", function(){
    dd(storage_path("app/credentials/healthbot-eynv-175558159099.json"));
});

Route::post("questions/store", [\App\Http\Controllers\QuestionsController::class,"store"]);