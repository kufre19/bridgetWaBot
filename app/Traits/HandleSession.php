<?php

namespace App\Traits;

use App\Models\Session;
use Illuminate\Support\Facades\Storage;




trait HandleSession
{
   

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

            $model = new Session();
            $fetch = $model->where('whatsapp_id', $this->userphone)->first();
            $this->user_session_data = json_decode($fetch->session_data, true);
        } else {

            $fetch = $model->where('whatsapp_id', $this->userphone)->first();
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
    public function remove_object_from_session($key="")
    {
        unset($this->user_session_data[$key]);
        $this->update_session($this->user_session_data);

    }

    public function continue_session_command()
    {
        $data = $this->user_session_data['active_command'];
        $command = $data['command'];
        $command_value = $data['command_value'];
        
     

    }

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

    

    public function run_action_session($action="")
    {
        $session = $this->user_session_data;
        $current_step_count = $session['current_step'];
        $current_step_to_run = $session['steps'][$current_step_count];
        $current_action_type = $current_step_to_run['action_type'];
        $current_action_value = $current_step_to_run['value'];

        $this->$current_action_type($current_action_value);
        
    }

    
    

 
   
}
