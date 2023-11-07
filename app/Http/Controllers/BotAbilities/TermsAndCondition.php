<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions ;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use App\Models\User;
use Illuminate\Http\Request;


class TermsAndCondition extends GeneralFunctions implements AbilityInterface
{

    public $steps = ["begin_func", "privacy_policy", "checkTermsSelection"];
    public $accepted = ['Accept','Decline'];
   



    public function begin_func()
    {
        // sets new route to this class
      
        $this->set_session_route("TermsAndCondition");
        $this->go_to_next_step();
        $this->continue_session_step();
    }

   

    public function privacy_policy()
    {
        $text = <<<MSG
        Before you proceed, you will need to read and accept our terms of use and privacy policy. 

        We will not ask you to share any sensitive information concerning your health or other aspect of your life. All you need to do is to ask questions about hypertension and we will provide accurate and factual answers. 
        This service does not replace the recommendations of your doctor. We provide education that will help you interact with your doctor better and also practice the recommendations that the doctor has provided. 
        MSG;
        
        $menu_obj = $this->MenuArrayToObj($this->accepted);
        $menu_txt = new TextMenuSelection($menu_obj);
        $menu_txt->send_menu_to_user($text);
        $this->go_to_next_step();
        $this->ResponsedWith200();
       
    }

    public function checkTermsSelection()
    {
        // this should check what user selected and end if decline else go back to Main Ability
        $response= $this->user_message_original;
        $menu_obj = $this->MenuArrayToObj($this->accepted);
        $menu_txt = new TextMenuSelection($menu_obj);

        $menu_txt->check_expected_response($response);

        if($response == "1" || $response =="Accept"){
            // update user accepted_term field and go back to main ability
            $this->update_Wa_user_terms();
            
            if($this->app_config_cred['category'] == "diabetes")
              {
                  // start the session for mainDiabetes
                  $diabetes_bot = new MainDiabetes();
                  $diabetes_bot->begin_func();
              }else{
                $main = new Main;
                $main->begin_func();
              }


        }

        if($response == "2" || $response =="Decline"){
            // clear session and end 
            $this->update_session();
            $this->ResponsedWith200();
            
        }

    }

    public function update_Wa_user_terms()
    {
        $usermodel = new User();
        $user = $usermodel->where('whatsapp_id',$this->userphone) ->where('bot_category', $this->app_config_cred['category'])
        ->update([
            "accepted_terms"=>"accepted"
        ]);

    }




    function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }
}
