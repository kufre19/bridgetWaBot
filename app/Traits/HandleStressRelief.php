<?php

namespace App\Traits;

use App\Models\SampleProducts;
use Illuminate\Support\Facades\Config;

trait HandleStressRelief {


    public function stress_relief_index($split_message)
    {
        $stress_relief_command = $split_message[0];
        $command_value = $split_message[1];

        if($command_value == "1")
        {
            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "stress_sample:2",
                        "title" => "Watch Video"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:str_heiress_aloom",
                        "title" => "FAQs"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "stress_relief_get_started:2",
                        "title" => "Buy Now"
                    ]
                ]

                

            ];
            $sensory_messasge = "Please select an action";
            $this->send_post_curl($this->make_button_message($this->userphone,"Heiress Aloom Serene",$sensory_messasge,$button));            
            die;

        }

        if($command_value == "2")
        {
            $button = [
               
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:str_senate_2",
                        "title" => "FAQs"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "stress_relief_get_started:3",
                        "title" => "Get Started"
                    ]
                ]

                

            ];
            $sensory_messasge = "Please select an action";
            $this->send_post_curl($this->make_button_message($this->userphone,"Sensate 2",$sensory_messasge,$button));            
            die;

        }
        if($stress_relief_command == "stress_sample")
        {
           $model = new SampleProducts();
           $fetch = $model->where('product_type',$command_value)->get();
           $count = count($fetch);


            if($count < 1)
            {
                $this->send_text_message("Sorry No samples available for this product!");
                $this->send_journey_menu();
                die;
                
            }else{
               foreach ($fetch as $key => $value) {
                $file_url = env("APP_URL")."storage/app/public/".$value->document_link;
                // $file_url = "https://naijaprojecthub.com/gabriella/gabriella_app/storage/app/public/uploads/1663166846.png";

                $this->send_media_message($value->document_type,$file_url,$value->description);
               }
               $this->send_journey_menu();
               die;
            }
        }

        if($stress_relief_command == "stress_relief_get_started")
        {
            // $this->send_text_message("Send user the Business Next Level Products to add to cart!");
            // die;
            $category_id = 2;
            $this->fetch_display_product("",$command_value);
        }
          

    }

}