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
        
        if (strpos($command_value, "cart_do") !== FALSE)
        {
            $this->cart_index(['cart_do','checkout']);
        }

        if (strpos($command_value, ":") !== FALSE)
        {
            $data = explode(":",$command_value);
            $to_do = $data[0];
            $to_do_value = $data[1];
            if (strpos($this->button_id, "stress_relief") !== FALSE) {
                $this->stress_relief_index($data);
            }
        }


    }

    public function handle_session_command($response_from_user)
    {
        $data = $this->user_session_data['active_command'];
        $command = $data['command'];
        $command_value = $data['command_value'];
        if (strpos($command, "cart") !== FALSE) {
            $this->add_command_to_session();
            $this->cart_index([$command, $command_value], $response_from_user);
            die;
        }

        if (strpos($command, "answer_question") !== FALSE)
        {
        $user_session = $this->user_session_data;
        $i = 0;
        foreach($user_session as $data)
        {
            if(is_array($data))
            {
                if(isset($data['question']))
                {
                    if($data['answer'] == "")
                    {
                       $this->add_command_to_session();
                       $user_session[$i]['answer'] = $response_from_user;
                       $this->check_questions();
                    }
                }

            }
            $i++;
        }
        }
        if (strpos($command, "create_choc") !== FALSE)
        {
            $data = $this->user_session_data['active_command'];
            $command = $data['command'];
            $command_value = $data['command_value'];
            $this->create_new_choc($command_value,$response_from_user);

        }

        if (strpos($command, "add_note") !== FALSE)
        {
            $data = $this->user_session_data;
            $this->add_new_object_to_session("order_note",$response_from_user);
            // $this->user_session_data[''] = ;
            // dd( $this->user_session_data);
            // $this->update_session( $this->user_session_data);
            $this->cart_index(["cart_do","checkout"]);


        }
        if (strpos($command, "send_link") !== FALSE)
        {
            $data = $this->user_session_data;
            $this->add_new_object_to_session("docs_links",$response_from_user);

            // $this->user_session_data['docs_links'] = $response_from_user;
            // dd( $this->user_session_data);
            // $this->update_session( $this->user_session_data);
            $this->cart_index(["cart_do","checkout"]);


        }
        if(strpos($command, "set_delivery_area") !== FALSE)
        {
            $data = Storage::disk('public')->get('areas.json');
            $areas_saved = json_decode($data,true);
            $fetched_area = $areas_saved[$response_from_user] ?? null;
            if($fetched_area == null)
            {
                $command = ["active_command" => true, "command" => "set_delivery_area", "command_value" => ""];
                $this->add_command_to_session($command);
                $this->send_text_message("please select your area for delivery from the menu below");
                $this->send_delivery_area();
                die;
            }
            $this->add_new_object_to_session("delivery_area",$fetched_area);
            $this->cart_index(["cart_do","checkout"]);



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

    public function add_new_journey($journey_name,$journey_value)
    {
        $data = $this->user_session_data;
        $new_journey = ["journey_name" =>$journey_name, "journey_value" =>$journey_value];
        if(!isset($data['journey']))
        {
            $this->add_new_object_to_session("journey",$new_journey);

        }else{
            $this->user_session_data['journey']  = $new_journey;
            $this->update_session($this->user_session_data);
        }
      
    }

 
    public function continue_journey()
    {
        $data =  $this->user_session_data['journey'];
        $journey_name = $data['journey_name'];
        $journey_value = $data['journey_value'];

        // dd($data);

        if($journey_name == "stress_relief")
        {
            if($journey_value == "show_menu")
            {
                $this->menu_item_id = "0:2";
                $this->menu_index();
            }

        }

        if($journey_name == "alovera")
        {
            if($journey_value == "show_menu")
            {
                $this->menu_item_id = "0:1";
                $this->menu_index();
            }

        }

        if($journey_name == "bnl")
        {
            $this->button_id = "bnl:".$journey_value;
            $this->bnl_index(["bnl",$journey_value]);
           
            

        }

    }
}
