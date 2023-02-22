<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Config;

trait HandleText
{
    use HandleButton, SendMessage;

    public $text_intent;

    public function text_index()
    {
        $this->find_text_intent();
        if ($this->text_intent == "greetings") {
            $this->send_greetings_message($this->userphone);
            die;
        }
        if ($this->text_intent == "menu") {
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
        }

        if ($this->text_intent == "show_cart") {
            $this->get_cart();
            die;
        }
        if ($this->text_intent == "show_order") {
            $this->handle_order_index(["order","menu"]);
        }
    }

    public function show_menu_message()
    {
    }

    public function register_user(array $data)
    {
        $model = new User();
    }

    public function determin_text()
    {
    }

    public function find_text_intent()
    {
        $message = $this->user_message_lowered;

        $greetings = Config::get("text_intentions.greetings");
        $menu = Config::get("text_intentions.menu");
        $show_cart = Config::get("text_intentions.show_cart");
        $show_order = Config::get("text_intentions.show_order");


        if (in_array($message, $greetings)) {
            $this->text_intent = "greetings";
        } elseif (in_array($message, $menu)) {
            $this->text_intent = "menu";
        }elseif (in_array($message, $show_cart)) {
            $this->text_intent = "show_cart";
        }elseif (in_array($message, $show_order)) {
            $this->text_intent = "show_order";
        }
         elseif (isset($this->user_session_data['active_command'])) {
            if (!empty($this->user_session_data['active_command'])) {
                $this->handle_session_command($message);
            }
        } else {
            $this->text_intent = "others";
        }
    }




    public function  runtest(array $data)
    {
        return $this->test_response($data);
    }
}
