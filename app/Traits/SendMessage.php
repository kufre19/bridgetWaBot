<?php

namespace App\Traits;
use Illuminate\Support\Facades\Storage;



trait SendMessage {
    use MessagesType;

    public function send_first_timer_message()
    {
        $to = $this->userphone;
        $message = <<<MSG
        Hello my name is Aura. Lovely to meet you {$this->username}. I want to remind you, today opens up many new possibilities for you.  Let's take the journey.
        Go ahead, have a look at our main menu of Products and services.
        MSG;

        $this->send_post_curl($this->make_text_message($to,$message));
        $this->send_post_curl($this->make_main_menu_message($to));       
        die;


    }

    public function send_greetings_message()
    {
        $to = $this->userphone;
        $text = <<<MSG
        Hello {$this->username}, Greetings from Heiress Aloom, welcome back.  
        How can we serve you with one or more of our products and services today. 
        MSG;
        $this->send_post_curl($this->make_text_message($to,$text));
        $this->send_post_curl($this->make_main_menu_message($to));       

        die;

    }

    public function send_next_business_level_menu()
    {
        $button = [
            [
                "type" => "reply",
                "reply" => [
                    "id" => "bnl:1",
                    "title" => "Sensory Marketing"
                ]
            ],
            [
                "type" => "reply",
                "reply" => [
                    "id" => "bnl:2",
                    "title" => "Customer Persona"
                ]
            ]


        ];
        $text = "Let's Get Started! \nSelect from the options below for more";
        $this->send_post_curl($this->make_button_message($this->userphone,"Business Next Level Menu",$text,$button));
        die;
    }
    public function send_alovera_menu()
    {
        $button = [
            [
                "type" => "reply",
                "reply" => [
                    "id" => "show_products:2",
                    "title" => "See Products"
                ]
            ],
            [
                "type" => "reply",
                "reply" => [
                    "id" => "faq_category:alovera",
                    "title" => "FAQs"
                ]
            ]


        ];
        $text = "Let's Get Started! \nSelect from the options below for more";
        $this->send_post_curl($this->make_button_message($this->userphone,"Business Next Level Menu",$text,$button));
        die;
    }
    public function send_stress_relief_menu()
    {
        $button = [
            [
                "type" => "reply",
                "reply" => [
                    "id" => "stress_relief:1",
                    "title" => "Heiress Aloom Serene"
                ]
            ],
            [
                "type" => "reply",
                "reply" => [
                    "id" => "stress_relief:2",
                    "title" => "Sensate 2"
                ]
            ]


        ];
        $text = "Let's Get Started! \nSelect from the options below for more  \nFill in the below when checking out cart for these products:
            \nName
            \nSurname
            \nAddress
            \nPostal code
            \nTelephone number";
        $this->send_post_curl($this->make_button_message($this->userphone,"Stress Relief Menu",$text,$button));
        die;
    }

    public function send_journey_menu()
    {
        $button = [
            [
                "type" => "reply",
                "reply" => [
                    "id" => "continue_journey",
                    "title" => "Continue Journey"
                ]
            ],
            [
                "type" => "reply",
                "reply" => [
                    "id" => "menu",
                    "title" => "Back To Menu"
                ]
            ]


        ];
        $text = "Select your next action";
        $this->send_post_curl($this->make_button_message($this->userphone,"Next action",$text,$button));
        die;
    }

    public function send_order_menu()
    {
        $button = [
            [
                "type" => "reply",
                "reply" => [
                    "id" => "order:awaiting",
                    "title" => "My Orders"
                ]
            ],
            [
                "type" => "reply",
                "reply" => [
                    "id" => "order:complete",
                    "title" => "Customer Persona"
                ]
            ]


        ];
        $text = "Let's Get Started! \nSelect from the options below for more";
        $this->send_post_curl($this->make_button_message($this->userphone,"Business Next Level Menu",$text,$button));
        die;
    }

    public function send_text_message($text,$to="")
    {
        if($to =="")
        {
            $to = $this->userphone;
        }
        $this->send_post_curl($this->make_text_message($to,$text));
        return response("",200);

    }

    public function send_media_message($type,$file_url,$caption=null)
    {
        switch ($type) {
            case 'video':
                $this->send_post_curl($this->make_video_message($this->userphone,$file_url,$caption));
                break;
            case 'image':
                $this->send_post_curl($this->make_image_message($this->userphone,$file_url,$caption));
                break;
            case 'document':
                $this->send_post_curl($this->make_document_message($this->userphone,$file_url,$caption));
                break;
            default:
                $this->send_text_message("An Error Occured with the media file! support will be notified");
                die;
                break;
        }

        return true;

    }

    public function send_delivery_area()
    {
        $data = Storage::disk('public')->get('areas.json');
        $areas_saved = json_decode($data,true);
        $menus =[];
        foreach ($areas_saved as $key => $value) {
           $data = [
            "id"=> $key,
            "title"=> $value['name'],
            "description"=> "shipping cost $". number_format($value['cost'],2) 
           ];
           array_push($menus,$data);
        }
        $new_message = $this->make_menu_message($this->userphone,$menus,"Areas","select area");
        $this->send_post_curl($new_message);
        die;

      
    }


    public function send_post_curl($post_data)
    {
        $token = env("WB_TOKEN");
        $url = env("WB_MESSAGE_URL");

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            "Authorization: Bearer {$token}"
        ),
        ));

        $response = curl_exec($curl);
        echo $response;

        // curl_close($curl);

    }
}