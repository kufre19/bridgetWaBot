<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use Illuminate\Http\Request;


class Main extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["begin_func", "test_main", ""];
    


    public function begin_func()
    {
        // echo"loozp";

        
       
    }

    public function test_main()
    {
       echo "done 1";
       $this->go_to_next_step();
      return  $this->continue_session_step();
       
    }

    public function test_main3()
    {
        echo "complete";
        exit(200);

    }

    


   


    function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }

   



}
