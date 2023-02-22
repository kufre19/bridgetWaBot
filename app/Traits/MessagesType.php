<?php

namespace App\Traits;


/* 
Button and menu code IDs and their meaning

starting with 0 = main menu item
starting with 1 = product item



*/

trait MessagesType {

   

  

    public function make_text_message($text,$to="",$preview_url=false)
    {
        if($to == "")
        {
            $to = (string)$this->userphone;
        }
       
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "text",
            "text"=> [
                "preview_url"=> $preview_url,
                "body"=> $text
            ]

        ];

        return json_encode($message);

    }

    public function make_button_message($to,$header_text,$body_text,$buttons,$preview_url=false)
    {
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "button",
                "header"=> [
                    "type"=> "text",
                    "text"=> $header_text
                ],
                "body"=> [
                    "text"=> $body_text
                ],
                "action"=> [
                    "buttons"=>$buttons
                    
                    
                ]
            ]

        ];

        return json_encode($message);

    }

  

    public function make_menu_message($menus,$to="",$text="",$button_name="Show Menu")
    {
        if($to == "")
        {
            $to = (string)$this->userphone;
        }

        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "list",
                "header"=> [
                    "type"=> "text",
                    "text"=> $text
                ],
                "body"=> [
                    "text"=> "Show Menu"
                ],
                "action"=> [
                    "button"=> $button_name,
                    "sections"=> [
                        [
                            "title"=> "Show Menu",
                            "rows"=> $menus
                        ],
                       
                    ]
                ]
            ]

        ];

        return json_encode($message);

    }

    public function make_video_message($to,$video_url,$caption=null)
    {
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "video",
            "video"=> [
                "link"=> $video_url,
                "caption"=> $caption
            ]

        ];
        return json_encode($message);

    }

    public function make_image_message($to,$image_url,$caption=null)
    {
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "image",
            "image"=> [
                "link"=> $image_url,
                "caption"=> $caption
            ]

        ];
        return json_encode($message);

    }
    public function make_document_message($to,$docs_url,$caption=null)
    {
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "document",
            "document"=> [
                "link"=> $docs_url,
                "caption"=> $caption
            ]

        ];
        return json_encode($message);

    }

    

  
}