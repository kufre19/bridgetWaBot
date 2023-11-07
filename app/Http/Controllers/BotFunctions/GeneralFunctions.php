<?php
namespace App\Http\Controllers\BotFunctions;

use App\Http\Controllers\BotAbilities\Main;
use App\Http\Controllers\BotController;
use App\Models\WaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use stdClass;
use Stichoza\GoogleTranslate\GoogleTranslate;


class GeneralFunctions extends BotController {
    public $username;
    public $user_session_data;
    public $userphone;
    public $user_message_original;
    

    // public function __construct($phone,$username,$user_message_original="")
    // {
    //     $this->userphone = $phone;
    //     $this->username = $username;
    //     info($user_message_original);
    //     $this->user_message_original = $user_message_original;
        
    // }

    public function __construct()
    {
        
        parent::__construct(session()->get("request_stored"));
        $this->app_config_cred = $this->get_meta_app_cred($this->wa_phone_id);
        $this->fetch_user_session();


    }

   


    public function set_properties($value,$property)
    {
        $this->$property = $value;
    }

   
    public function message_user($message,$phone="")
    {
        if($phone=="")
        {
            $phone = $this->userphone;
        }

        $text = $this->make_text_message($message,$phone);
        $this->send_post_curl($text);
    }

    public function message_user_btn($body,$phone="",$header,$btn)
    {
        if($phone=="")
        {
            $phone = $this->userphone;
        }

        $text = $this->make_button_message($phone,$header,$body,$btn);
        $this->send_post_curl($text);
    }


    public function MenuArrayToObj($menu_items_arr)
    {
        $obj = new stdClass();
        foreach ($menu_items_arr as $value) {
            $obj->{$value} = ['name' => $value];
        }
        return $obj;
    }

    public function get_meta_app_cred($wa_phone_id)
    {
        $wa_config = Config::get("whatsapp_config");
        $app_config = $wa_config[$wa_phone_id];

        $app_config['url'] = "https://graph.facebook.com/{$app_config['version']}/{$wa_phone_id}/messages";

        return $app_config;
    }


  


   

   

    

}