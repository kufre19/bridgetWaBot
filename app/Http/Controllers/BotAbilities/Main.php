<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use Illuminate\Http\Request;


class Main extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["begin_func", "", ""];
   



    public function begin_func()
    {
        // sets new route to this class
      
        $this->set_session_route("Main");
        $this->go_to_next_step();
        $this->continue_session_step();
    }

   



    function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }
}
