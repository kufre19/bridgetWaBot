<?php

namespace App\Traits;

use App\Models\FaqsModel;

trait HandleFaq
{
    use MessagesType, SendMessage;
    public function faq_index($command)
    {
        $faq_command = $command[0];
        $command_value = $command[1];

        if ($faq_command == "faq_category") 
        {
            $faqs = $this->fetch_faq_by_category($command_value);
            
            foreach ($faqs as $key => $value) {
                $button = [
                    [
                        "type" => "reply",
                        "reply" => [
                            "id" => "show faq:{$value->id}",
                            "title" => "Show FAQs"
                        ]
                    ],
                ];
                $button_message = $this->make_button_message($this->userphone,"FAQ",$value->question,$button);
                $this->send_post_curl($button_message);
            }
        }
    }


    public function fetch_faq_by_category($category)
    {
        $model = new FaqsModel();
        $fetch = $model->where('category', $category)->get();
        $count = count($fetch);
        // dd("i'm here");
        // $this->send_text_message("No faq for this product currently!");

        if ($count < 1) {
            $this->send_text_message("No faq for this product currently!");
            $this->send_journey_menu();
            die;
        } else {
            return $fetch;
        }
    }

    public function send_faq_categories()
    {
        $menu_list = [
            [
                "id" => "faq_category:bnl_persona",
                "title" => "Customer Persona",
                "description" => "faqs for business next level heiress customer perona product"
            ],
            [
                "id" => "faq_category:bnl_sensory",
                "title" => "Sensory Marketing",
                "description" => "faqs for business next level sensory marketing product"
            ],
            [
                "id" => "faq_category:str_heiress_aloom",
                "title" => "Heiress Aloom Serene",
                "description" => "faqs for aloom product"
            ],
            [
                "id" => "faq_category:str_senate_2",
                "title" => "Sensate 2",
                "description" => "faqs for sensate 2 product"
            ],
            [
                "id" => "faq_category:alovera",
                "title" => "Aloe Vera Creamed Honey",
                "description" => "faqs for aloe vera creamed honey  product"
            ],



        ];
        $menu_message = $this->make_menu_message($this->userphone, $menu_list,"FAQs","FAQ Menu");
        $this->send_post_curl($menu_message);
    }
}
