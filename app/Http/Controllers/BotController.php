<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Traits\GeneralFunctions;
use App\Traits\HandleButton;
use App\Traits\HandleCart;
use App\Traits\HandleImage;
use App\Traits\HandleMenu;
use App\Traits\HandleSession;
use App\Traits\HandleText;
use App\Traits\MessagesType;
use App\Traits\SendMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    use HandleText, HandleButton, HandleMenu, SendMessage, MessagesType, HandleSession, GeneralFunctions, HandleImage;

    public $user_message_original;
    public $user_message_lowered;
    public $button_id;
    public $menu_item_id;
    public $username;
    public $userphone;
    public $userfetched;
    public $message_type;
    public $user_session_data;
    public $user_session_status;
    public $wa_image_id;
    public $user_subscription;
    public $wa_phone_id;
    public $app_config_cred;


    /* 
    @$menu_item_id holds the id sent back from selecting an item from whatsapp
    @
    
    e
    */

    public function __construct(Request $request)
    {
        $this->store_request_obj($request);

        // $this->LogInput($request->all());
        if (!isset($request['hub_verify_token'])) {


            // Any code here that might throw an exception.
            if (isset($request['entry'][0]['changes'][0]['value']['statuses'])) {
                $this->message_type = "stop";
                // $this->LogInput($request->all());
            } else {
                // Remaining initialization code that depends on $request['entry'] being set...


                $this->username = $request['entry'][0]['changes'][0]["value"]['contacts'][0]['profile']['name'] ?? "there";
                $this->userphone = $request['entry'][0]['changes'][0]["value"]['contacts'][0]['wa_id'];
                $this->wa_phone_id = $request['entry'][0]['changes'][0]["value"]['metadata']["phone_number_id"];


                // info($request);

                if (isset($request['entry'][0]['changes'][0]["value"]['messages'][0]['text'])) {
                    $this->user_message_original = $request['entry'][0]['changes'][0]["value"]['messages'][0]['text']['body'];
                    $this->user_message_lowered  = strtolower($this->user_message_original);
                    $this->message_type = "text";
                }

                if (isset($request['entry'][0]['changes'][0]["value"]['messages'][0]['image'])) {
                    $this->wa_image_id = $request['entry'][0]['changes'][0]["value"]['messages'][0]['image']['id'];
                    $this->message_type = "image";
                }


                if (isset($request['entry'][0]['changes'][0]["value"]['messages'][0]['interactive'])) {
                    $interactive_type = $request['entry'][0]['changes'][0]["value"]['messages'][0]['interactive']['type'];
                    switch ($interactive_type) {
                        case 'list_reply':
                            $this->menu_item_id = $request['entry'][0]['changes'][0]["value"]['messages'][0]['interactive']['list_reply']['id'];
                            $this->message_type = "menu";

                            break;

                        case 'button_reply':
                            $this->button_id = $request['entry'][0]['changes'][0]["value"]['messages'][0]['interactive']['button_reply']['id'];
                            $this->message_type = "button";

                            break;


                        default:
                            dd("unknow command");
                            break;
                    }
                }
            }
        }
    }


    public function index(Request $request)
    {
        if ($this->message_type == "stop") {
            http_response_code(200);
            return exit(200);
        }

        if (isset($request['hub_verify_token'])) {
            return $this->verify_bot($request);
        }

        $this->app_config_cred = $this->get_meta_app_cred($this->wa_phone_id);



        $this->fetch_user();
        switch ($this->message_type) {
            case 'text':
                $this->text_index();
                break;

            case 'button':

                $this->button_index();
                break;

            case 'menu':
                $this->menu_index();
                break;
            case 'image':
                $this->image_index();
                break;

            default:
                die;
                break;
        }
    }


    public function test(Request $request)
    {
        if (isset($request['hub_verify_token'])) {
            return $this->verify_bot($request);
        }

        $this->send_text_message($this->user_message_original);
        die;
    }


    public function fetch_user()
    {
        $model = new User();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('bot_category', $this->app_config_cred['category'])
            ->where("subscription", "active")
            ->first();

        if ($this->app_config_cred['category'] == "diabetes") {
            if ($fetch) {
                $this->fetch_user_session();
            } else {
                $message = "Sorry this account does not have an active subscription";
                $data = $this->make_text_message($message,$this->userphone);
                $this->send_post_curl($data);
               return $this->ResponsedWith200();
            }
        } else {
            if (!$fetch) {
                $this->register_user();
            } else {

                $this->fetch_user_session();
            }
        }
    }


    public function register_user()
    {
        $model = new User();
        $model->name = $this->username;
        $model->whatsapp_id = $this->userphone;
        $model->bot_category = $this->app_config_cred['category'];
        $model->save();
        $this->start_new_session();
        // $this->send_greetings_message();

    }

    public function get_meta_app_cred($wa_phone_id)
    {
        $wa_config = Config::get("whatsapp_config");
        $app_config = $wa_config[$wa_phone_id];

        $app_config['url'] = "https://graph.facebook.com/{$app_config['version']}/{$wa_phone_id}/messages";

        return $app_config;
    }


    public function verify_bot(Request $input)
    {
        if (isset($input['hub_verify_token'])) { ## allows facebook verify that this is the right webhook
            $token  = env("VERIFY_TOKEN");
            if ($input['hub_verify_token'] === $token) {
                return $input['hub_challenge'];
            } else {
                echo 'Invalid Verify Token';
                http_response_code(500);
                return exit(500);
            }
        }
    }

    public function store_request_obj(Request $request)
    {
        session()->put("request_stored", $request);
    }
    // for testing purposes
    public function LogInput($data)
    {

        $data = json_encode($data);
        $file = time() . rand() . '_file.json';
        $destinationPath = public_path() . "/upload/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        File::put($destinationPath . $file, $data);
    }
}
