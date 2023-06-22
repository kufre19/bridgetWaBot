<?php

namespace App\Traits;

use App\Models\FaqsModel;
use Illuminate\Support\Facades\Config;

trait HandleButton
{
    use SendMessage;

    public function button_index()
    {
        if (isset($this->user_session_data['run_action_step'])) {
            if ($this->user_session_data['run_action_step'] == 1) {
                $this->continue_session_step();
            }
        }
    }
    


    public function get_command_and_value_button()
    {
        $data = explode(":", $this->button_id);
        return $data;
    }

    public function determin_button()
    {
    }



    public function test_response(array $data)
    {
        dd($this->username);
    }
}
