<?php
namespace App\Traits;

use App\Models\SampleProducts;
use Illuminate\Support\Facades\Config;

trait HandleNextLevelBusiness{
    use SendMessage,HandleCart;

    public function bnl_index($split_message)
    {

        $bnl_command = $split_message[0];
        $command_value = $split_message[1];
       
        if($command_value == "1" && $bnl_command == "bnl")
        {
            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "bnl_sample:4",
                        "title" => "Sample Audio"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:bnl_sensory",
                        "title" => "FAQs"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "bnl_get_started:4",
                        "title" => "Get Started"
                    ]
                ]

                

            ];
            $sensory_messasge = Config::get("bnl_messages.sensory_message");
            $this->send_post_curl($this->make_button_message($this->userphone,"Sensory Marketing",$sensory_messasge,$button));
            $this->add_new_journey("bnl",$command_value);

            die;
        }
        if($command_value == "2" && $bnl_command == "bnl")
        {
            $button = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "bnl_sample:5",
                        "title" => "Show Samples"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "faq_category:bnl_persona",
                        "title" => "FAQs"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "bnl_get_started:5",
                        "title" => "Get Started"
                    ]
                ],
            ];
            $sensory_messasge = Config::get("bnl_messages.customer_persona_message");
            $this->send_post_curl($this->make_button_message($this->userphone,"Customer Persona",$sensory_messasge,$button));
            $this->add_new_journey("bnl",$command_value);

            die;
        }

        if($bnl_command == "bnl_sample")
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
               foreach ($fetch as $key => $value) {#loop through all the sample for a prod
                $file_url = env("APP_URL")."storage/app/public/".$value->document_link;
                // $file_url = "https://naijaprojecthub.com/gabriella/gabriella_app/storage/app/public/uploads/1663166846.png";

                $this->send_media_message($value->document_type,$file_url,$value->description);
                
                // sleep(2);
               }
               $this->send_journey_menu();
               die;
            }
        }

        if($bnl_command == "bnl_get_started")
        {
            // $this->send_text_message("Send user the Business Next Level Products to add to cart!");
            // die;
            $category_id = 3;
            // dd($command_value);
            $this->fetch_display_product("",$command_value);
            die;
        }
    }
}