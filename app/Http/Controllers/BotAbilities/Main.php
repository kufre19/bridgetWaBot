<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use App\Models\User;
use Illuminate\Http\Request;


class Main extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["begin_func", "", ""];
    public $accepted_terms;



    public function begin_func()
    {
        // should first check if user accepted terms and conditon
        // if accepeted send intro message and then question
        // else should send the privacy policy question
        // sets new route to this class

        if($this->accepted_terms == "accepted")
        {
            // send intro and list of questions
            $this->set_session_route("Main");
            $this->intro_statement();
            // $this->go_to_next_step();
            $this->ResponsedWith200();
        }else{
            $terms_andcondition = new TermsAndCondition();
            $terms_andcondition->begin_func();
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
