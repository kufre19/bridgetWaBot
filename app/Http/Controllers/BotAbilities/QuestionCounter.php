<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use Illuminate\Http\Request;


class QuestionCounter extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["checkQsCount", "init_flow", "test_main3"];
    public const QS_COUNT = "qs_count";
    


    public function begin_func()
    {
        // sets new route to this class
        // and head to another method to ask for approval to begin flow
       
       
    }

    public function init_flow()
    {

    }


    public function checkQsCount()
    {
        // this should fetch user sesssion and check for a qs_count object/key in the session if not found create new one and
        // start count from 1

        $user_session = $this->user_session_data;
        if(isset($user_session[self::QS_COUNT]))
        {
            // check if it's up to five
            $counter = $user_session[self::QS_COUNT];
            if($counter == 1)
            {
                // then start route to ask questions
                $text = "start flow";
                $this->send_post_curl($this->make_text_message($text,$this->userphone));
                $this->ResponsedWith200();
            }
        }else{
            $this->user_session_data[self::QS_COUNT] = 0;
            $this->ResponsedWith200();
        }
    }


   


    function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }

   



}
