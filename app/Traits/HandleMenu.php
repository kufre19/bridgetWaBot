<?php

namespace App\Traits;

use App\Models\FaqsModel;
use Illuminate\Support\Facades\Config;

trait HandleMenu {
    use SendMessage, HandleCart, HandleFaq;

    public function menu_index()
    {
        
        if($this->menu_item_id == "faq")
        {
            
            $model = new FaqsModel();
            $fetch = $model->get();
            $count = count($fetch);
            if($count < 1)
            {
                $this->send_text_message("Sorry No Faqs Available");
                die;
            }else{
                
                $this->send_faq_categories();
                die;
            }
        }elseif (strpos($this->menu_item_id,"faq_category") !== FALSE) {
            $command = $this->get_command_and_value_menu();
            $this->faq_index($command);
            $this->send_journey_menu();

        }
        elseif ($this->menu_item_id == "0:1") {
            $command = $this->get_command_and_value_menu();
            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:alovera",
                        "title" => "FAQs"
                    ]
                ]
    
            ];
            $data = $this->make_button_message($this->userphone,"FAQ","See FAQs For this Product",$button);
            $this->fetch_display_product($command[1]);
            $this->send_post_curl($data);
            $this->add_new_journey("alovera","show_menu");
            die;
        }
        elseif ($this->menu_item_id == "0:2") {
            $command = $this->get_command_and_value_menu();
            $button = [
                // [
                //     "type" => "reply",
                //     "reply" => [
                //         "id" => "stress_sample:2",
                //         "title" => "See Samples"
                //     ]
                // ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:str_heiress_aloom",
                        "title" => "FAQs"
                    ]
                ]
               
            ];
            $data = $this->make_button_message($this->userphone,"Sress Relief","See FAQs And For this Product",$button);
            $this->add_new_journey("stress_relief","show_menu");
            $this->fetch_display_product($command[1]);
            $this->send_post_curl($data);
            die;
        }elseif ($this->menu_item_id == "0:3") {
            $this->send_next_business_level_menu();
           
            die;
        }elseif ($this->menu_item_id == "cart_show") {
           $this->get_cart();
           die;
        }
        elseif ($this->menu_item_id == "show_privacy_policy") {
            
           $message = Config::get("extra_messages.policy");
        //    dd($message);
           $this->send_text_message($message);
           die;
        }elseif ($this->menu_item_id == "show_tos") {
            
            $message = Config::get("extra_messages.tos");
         //    dd($message);
            $this->send_text_message($message);
            die;
        }elseif (strpos($this->menu_item_id,"order") !== FALSE) {
            
            $command = $this->get_command_and_value_menu();
         //    dd($message);
            $this->handle_order_index($command);
            die;
        }elseif (isset($this->user_session_data['active_command'])) {
            if (!empty($this->user_session_data['active_command'])) {
                $this->handle_session_command($this->menu_item_id);
            }
        }
    }


    public function determin_menu()
    {
        
    }

    public function get_command_and_value_menu()
    {
        $data = explode(":",$this->menu_item_id);
        return $data;
    }
    
}