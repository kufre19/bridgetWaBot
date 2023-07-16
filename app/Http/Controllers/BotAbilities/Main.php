<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\Questions;
use App\Models\ScheduleMenu;
use App\Models\User;
use Illuminate\Http\Request;


class Main extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["begin_func", "makeQuestionList", ""];
    public $accepted_terms;
    public $question_arr_list = [];



    public function begin_func()
    {
        // should first check if user accepted terms and conditon
        // if accepeted send intro message and then question
        // else should send the privacy policy question
        // sets new route to this class
        $this->get_Wa_user();

        if($this->accepted_terms == "accepted")
        {
            // send intro and list of questions
            $this->set_session_route("Main");
            $this->go_to_next_step();
            $this->continue_session_step();
        }else{
            $terms_andcondition = new TermsAndCondition();
            $terms_andcondition->begin_func();
        }



      
        
    }

    public function makeQuestionList()
    {
        $question_model = new Questions();
        $questions = $question_model->where("category",$this->app_config_cred['category'])->get();


        foreach ($questions as $key => $question) {
            array_push($this->question_arr_list,$question->questions);

        }


    }

    public function splitMessage($text, $limit = 4096)
    {

        if (strlen($text) > $limit) {
            $parts = str_split($text, ceil(strlen($text) / 2));
            $part1 = $parts[0];
            $part2 = $parts[1];

            return [$part1, $part2];
        } else {
            return [$text];
        }
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
