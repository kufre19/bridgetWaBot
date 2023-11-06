<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\Answers;
use App\Models\Questions;
use App\Models\ScheduleMenu;
use App\Models\User;
use Illuminate\Http\Request;

class MainDiabetes extends BotFunctionsGeneralFunctions implements AbilityInterface
{
    public $steps = ["begin_func","sendAnswers"];
    public $accepted_terms;
    public $question_arr_list = [];

    public function begin_func()
    {
        $this->get_Wa_user();

        if($this->accepted_terms == "accepted")
        {
            // send intro and list of questions
            $this->set_session_route("MainDiabetes");
            $this->go_to_next_step();
            $this->continue_session_step();
        }else{
            $terms_andcondition = new TermsAndCondition();
            $terms_andcondition->begin_func();
        }
    }

    // 

    public function makeQuestionSession()
    {


    }

    public function updateAllowedQuestions()
    {
        // this to fetch a question based on the corresponding number from number 
    }

    public function sendAnswers()
    {
        // check for progress first
        // if question_asked is empty then user just started then send first question to user and expect a response of one
        // if it's not empty then check user sent response if can be found in question asked list
        // and then in table as corresponding_number for question if found in the previous list
        // if not found check as question 
        // if found store the value of corresponding number and
        // increement the value for corresponding number asked by 1 then store

        $question_progress = $this->getQuestionProgress();
        if(!empty($question_progress['questions_asked']))
        {
            // user did not just start conversation

            if(in_array($this->user_message_original,$question_progress['questions_asked']))
            {

                // user asked/ pressed authorized question/number
            }else {
                // user response not authorized might just kill the flow here
            }
        }else {
            // user just started conversation
            $question_progress['questions_asked'][] = 1;
            $message = "Press 1 to get started with your learning journey!";
            $text = $this->make_text_message($message, $this->userphone);
            $send = $this->send_post_curl($text);       
         }
    }

    public function getQuestionProgress()
    {
        $question_progress = $this->user_session_data['question_progress'] ?? "";
        // createa the object if it's empty
        if($question_progress == ""){
            $question_progress = [
                "category"=>$this->app_config_cred['category'],
                "sub_category"=>1,
                "sub_cat_limit"=>$this->app_config_cred['no_of_sub_cat'],
                "questions_asked"=>[],
                "sub_cats_finished"=>[],
            ];
            $this->add_new_object_to_session("question_progress",$question_progress);
        }

        return $question_progress;
    }
    public function get_Wa_user()
    {
        $usermodel = new User();
        $user = $usermodel->where('whatsapp_id',$this->userphone)->first();

        $this->accepted_terms = $user->accepted_terms;

    }

    public function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }

}
