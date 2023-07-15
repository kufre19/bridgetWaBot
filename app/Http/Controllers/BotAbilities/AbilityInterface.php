<?php

namespace App\Http\Controllers\BotAbilities;


interface AbilityInterface {
    
    public function begin_func();
    // public function perform_selection();
    public function call_method($key);

    /**
     * this will allow for this functionality be called or initaiated without creating the object first
     */
    // public static function runThisFunctionAnon();

   
}