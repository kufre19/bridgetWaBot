<?php

namespace App\Traits;

use App\Models\FaqsModel;
use Illuminate\Support\Facades\Config;

trait HandleButton
{
    use SendMessage, HandleCart, HandleNextLevelBusiness, HandleStressRelief;

    public function button_index()
    {
        if (strpos($this->button_id, "show faq") !== FALSE) {
            $split_data = explode(":", $this->button_id);
            $faq_id = $split_data[1];
            $model = new FaqsModel();
            $fetch = $model->select('answer', 'question')->where('id', $faq_id)->first();
            if (!$fetch) {
                $this->send_text_message("Sorry This FAQ Is Not Available");
                die;
            } else {
                $text = <<<MSG
                Q: {$fetch->question}

                ANS:{$fetch->answer} 

                MSG;
                $this->send_text_message($text);
                $this->send_journey_menu();
                die;
            }

        }
        if (strpos($this->button_id, "cart") !== FALSE) {
            $data = $this->get_command_and_value_button();
            $this->cart_index($data);
        }

        if (strpos($this->button_id, "bnl") !== FALSE) {
            $data = $this->get_command_and_value_button();
            $this->bnl_index($data);
        }

        if (strpos($this->button_id, "stress_relief") !== FALSE) {
            $data = $this->get_command_and_value_button();
            $this->stress_relief_index($data);
        }

        if (strpos($this->button_id, "faq_category") !== FALSE) {
            $command = $this->get_command_and_value_button();
            $this->faq_index($command);
            $this->send_journey_menu();
        }
        if (strpos($this->button_id, "order") !== FALSE) {
            $command = $this->get_command_and_value_button();
            $this->handle_order_index($command);
        }
        if (strpos($this->button_id, "menu") !== FALSE) {
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
        }

        if ($this->button_id == "agreement") {
            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "cart_do:checkout",
                        "title" => "I Agree"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "do not agree",
                        "title" => "I Do Not Agree"
                    ]
                ]

            ];
            $text = <<<MSG
            Before we continue with your checkout please read and agree to our Terms of Service and Privacy Policy, links below

            Terms Of Service: https://heiressaloom.com/terms-of-service

            Privacy Policy: https://heiressaloom.com/privacy-policy
            MSG;
            $data = $this->make_button_message($this->userphone, "User Agreement", $text, $button);
            $this->send_post_curl($data);
            die;
        }
        if ($this->button_id == "do not agree") {
            $msg = Config::get("extra_messages.no_agree");
            $text = <<<MSG
            $msg
            MSG;
            $this->send_text_message($text);

            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "cart_do:checkout",
                        "title" => "I Agree"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "do not agree",
                        "title" => "I Do Not Agree"
                    ]
                ]

            ];
            $text = <<<MSG
                Before we continue with your checkout please read and agree to our Terms of Service and Privacy Policy, links below
    
                Terms Of Service: https://heiressaloom.com/terms-of-service
    
                Privacy Policy: https://heiressaloom.com/privacy-policy
                MSG;
            $data = $this->make_button_message($this->userphone, "User Agreement", $text, $button);
            $this->send_post_curl($data);
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
        }
        if ($this->button_id == "continue_journey") {
            $this->continue_journey();
        }

        if ($this->button_id == "menu") {
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
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
