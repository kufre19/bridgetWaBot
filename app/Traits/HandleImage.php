<?php

namespace App\Traits;


trait HandleImage
{
    public $image_intent;

    public function image_index()
    {
        $wa_image_id = $this->wa_image_id;
        $this->find_image_intent();
        if ($this->image_intent == "run_action_steps") {
            $this->continue_session_step();
        }
    }


    public function find_image_intent()
    {
        if (isset($this->user_session_data['run_action_step'])) {
            if ($this->user_session_data['run_action_step'] == 1 ) {
                $this->image_intent = "run_action_steps";
            }
        } else {
            $this->image_intent = "others";
        }
         
    }


}