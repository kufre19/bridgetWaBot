<?php

namespace App\Traits;

use App\Models\Session;
use Illuminate\Support\Facades\Storage;




trait HandleSession
{
   
    public static $EXPECTED_RESPONSES = "expected_response";
    

    /*
    Session codes
    0 ---- does not exist
    1 ---- active
    2 ---- expired 

    the 'continuation' field represenmts a command that should be continued after an action
    
    */





    public function session_index()
    {
    }


    public function start_new_session()
    {
        $data = [];
        $json = json_encode($data);
        $model = new Session();
        $model->whatsapp_id = $this->userphone;
        $model->session_data = $json;
        $model->expires_in = time() + 3600;
        $model->bot_category = $this->app_config_cred['category'];
        $model->save();
    }

    public function did_session_expired()
    {
        $model = new Session();
        $fetch = $model->select('expires_in')->where('whatsapp_id', $this->userphone)->first();

        if (!$fetch) {
            $this->user_session_status = 0;
            return $this->start_new_session();
        } elseif ($fetch->expires_in < time()) {
            $this->user_session_status = 2;
            return true;
        } else {
            $this->user_session_status = 1;
        }
    }

    /**
     * update user session by passing in a session data or leave it empty to reset
     * 
     */
    public function update_session($data = null)
    {
        if ($data == null) {
            $data = [];
            $data = json_encode($data);
        } else {
            $data = json_encode($data);
        }

        $model = new Session();
        $model->where('whatsapp_id', $this->userphone)
              ->where('bot_category', $this->app_config_cred['category'])
            ->update([
                'session_data' => $data,
                'expires_in' => time() + 3600
            ]);
            
        $this->fetch_user_session();
    }

    public function fetch_user_session()
    {
        $model = new Session();
        if ($this->did_session_expired()) {

            echo $this->app_config_cred['category'];

            $model = new Session();
            $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('bot_category', $this->app_config_cred['category'])->first();
            $this->user_session_data = json_decode($fetch->session_data, true);
        } else {

            $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('bot_category', $this->app_config_cred['category'])->first();
            $this->user_session_data = json_decode($fetch->session_data, true);
        }
    }

    public function add_command_to_session($data = null)
    {
        if ($data == null) {
            $this->user_session_data['active_command'] = array();
        } else {
            $this->user_session_data['active_command'] = $data;
        }
        $this->update_session($this->user_session_data);
    }

    public function add_new_object_to_session($key="",$value="")
    {
       if($key == "")
       {
        array_push($this->user_session_data,$value);
        $this->update_session($this->user_session_data);
       }else {
        $this->user_session_data[$key] = $value;
        $this->update_session($this->user_session_data);
       }

    }

    public function get_stored_answer($key)
    {

    }
    public function remove_object_from_session($key="")
    {
        unset($this->user_session_data[$key]);
        $this->update_session($this->user_session_data);

    }
    
    /**
     * this will continue a command given to the bot not a series of action
     * 
     */

    public function continue_session_command()
    {
        $data = $this->user_session_data['active_command'];
        $command = $data['command'];
        $command_value = $data['command_value'];
        
     

    }

    /**
     * this will continue whatever session activity(series of action) that's going on
     */
    public function continue_session_step($action="")
    {
        $this->run_action_session();
    
    }


    public function handle_session_command($response_from_user)
    {
        $data = $this->user_session_data['active_command'];
        $command = $data['command'];
        $command_value = $data['command_value'];

        if (strpos($command, "create_choc") !== FALSE)
        {
            $data = $this->user_session_data['active_command'];
            $command = $data['command'];
            $command_value = $data['command_value'];
            $this->create_new_choc($command_value,$response_from_user);

        }

        if (strpos($command, "continuation") !== FALSE)
        {
          $this->continue_session_command();

        }


        
    }
    public function make_new_session_global($data)
    {
        $this->user_session_data = $data;
    }

    /**
     * should create a new route session with the class that has be initialized or if session is created then change the 
     * class name and counter only
     */
    public function set_session_route($name)
    {
        
        if (isset($this->user_session_data['run_action_step'])) {
            if ($this->user_session_data['run_action_step'] == 1 ) {
                $this->change_route_name($name);
            }
        }else{
            $session_data = [
                "step_name"=>$name,
                "answered_questions" => [],
                "run_action_step"=>1,
                "current_step" => 0,
                "next_step" => 1,
                "last_operation_status"=>0,
                "form_counter"=>0,
               
            ];
    
            return $this->update_session($session_data);
        }
        
    }



    public function change_route_name($route_name)
    {
        $this->user_session_data["step_name"] = $route_name;
        $this->user_session_data["current_step"] = 0;
        $this->user_session_data["form_counter"] = 0;

        $this->update_session($this->user_session_data);
    }

    
    public function run_action_session($action="")
    {
        $session = $this->user_session_data;

        $namespace = '\App\Http\Controllers\BotAbilities\\';
        $class_name = $session['step_name'];
    
        // Combine the namespace and class name into a fully qualified class name
        $fully_qualified_class_name = $namespace . $class_name;
    
        // Create an object of the class/
        $obj = new $fully_qualified_class_name();
        // $obj->set_properties($this->user_session_data,"user_session_data");
        $obj->call_method($session['current_step']);
        
    }

    
    

 
   
}
