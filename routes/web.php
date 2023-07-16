<?php

use App\Http\Controllers\BotAbilities\Main;
use App\Models\Questions;
use Illuminate\Support\Facades\Config;
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
    $counter = 1;
    // $menu_messages = [["message","menu text"]...];
        // 
        $menu_messages = [];
        $intro_mesasges = Config::get("intro_messages");
        $specific_intro_messages = $intro_mesasges["hypertension"];
        // loop through the sub categories to get keys and intro messages keys are to be used for also checking what sub cat a question
        // belongs to
        foreach ($specific_intro_messages as $sub_category => $intro_message) {
          

            
            $question_model = new Questions();
            $questions = $question_model->get();
           
            

          
            $menu_txt = "";
            foreach ($questions as $key => $value) {
                if($value->sub_category == $sub_category){
                    $menu_txt  .="{$counter }. " .  $value->questions  . "\n". "\n";
                    $counter++;

                    
                   
                }
                array_push($menu_messages,["message"=>$intro_message,"menu_text"=>$menu_txt]);
                dd($menu_messages);
               
           

            }
           
        }
});

Route::post("questions/store", [\App\Http\Controllers\QuestionsController::class,"store"]);
Route::get("list-questions",[\App\Http\Controllers\QuestionsController::class,"list"]);
Route::get("edit-questions/{id}",[\App\Http\Controllers\QuestionsController::class,"edit"]);
Route::post("store/edit-questions",[\App\Http\Controllers\QuestionsController::class,"store_edit"]);

