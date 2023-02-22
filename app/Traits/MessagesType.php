<?php

namespace App\Traits;


/* 
Button and menu code IDs and their meaning

starting with 0 = main menu item
starting with 1 = product item



*/

trait MessagesType {

    public function make_main_menu_message($to,$text="")
    {
        if($text == "")
        {
            $text = "Choose from the list below";
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
                    "text"=> "Menu"
                ],
                "body"=> [
                    "text"=> $text
                ],
                "action"=> [
                    "button"=> "Main Menu",
                    "sections"=> [
                        [
                            "title"=> "Menu",
                            "rows"=> [
                                [
                                    "id"=> "0:1",
                                    "title"=> "Aloe Vera Creamed Honey",
                                    "description"=> ""
                                ],
                                [
                                    "id"=> "0:2",
                                    "title"=> "Stress Relief",
                                    "description"=> ""
                                ],
                                [
                                    "id"=> "0:3",
                                    "title"=> "Business next level",
                                    "description"=> ""
                                ],
                                [
                                    "id"=> "faq",
                                    "title"=> "FAQ",
                                    "description"=> ""
                                ],
                                [
                                    "id"=> "cart_show",
                                    "title"=> "My Cart",
                                    "description"=> "Get Your Available Cart"
                                ],
                                [
                                    "id"=> "show_tos",
                                    "title"=> "TOS",
                                    "description"=> "Our Terms Of Service"
                                ],
                                [
                                    "id"=> "show_privacy_policy",
                                    "title"=> "Privacy Policy",
                                    "description"=> "Our Privacy Policy"
                                ],
                                
                                [
                                    "id"=> "order:menu",
                                    "title"=> "My Order",
                                    "description"=> "Get Information About Your Orders"
                                ]
                            ]
                        ],
                       
                    ]
                ]
            ]

        ];

        return json_encode($message);

    }

  

    public function make_text_message($to,$text,$preview_url=false)
    {
       
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

    public function make_product_message($to,$body_text,$buttons,$preview_url,$image=null)
    {
        if ($image == null) {
           $image = "https://naijaprojecthub.com/gabriella/gabriella_app/storage/app/public/uploads/1663166846.png";
        }
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "button",
                "header"=> [
                    "type"=> "image",
                    "image"=> [
                        "link"=>$image
                        ]
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

    public function make_menu_message($to,$menus,$text="",$button_name="")
    {

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
                    "text"=> "Choose An Item From the Menu"
                ],
                "action"=> [
                    "button"=> $button_name,
                    "sections"=> [
                        [
                            "title"=> "Select An Item",
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

    public function make_multi_product_message($to,$header_text,$body_text,$products,$catalog_id="",$header_type="text")
    {
        if($catalog_id == "" )
        {
            $catalog_id = env("WB_CATALOG_ID");
        }
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "product_list",
                "header"=> [
                    "type"=> $header_type,
                    "text"=> $header_text
                ],
                "body"=> [
                    "text"=> $body_text
                ],
                "action"=> [
                    "catalog_id"=> $catalog_id,
                    "sections"=>[[
                        "title"=> "Products",
                        "product_items"=>$products
                        ]
                    ]
                    
                    
                ]
            ]

        ];

        return json_encode($message);

    }

    public function make_single_product_message($to,$body_text,$products_id,$catalog_id="")
    {
        if($catalog_id == "" )
        {
            $catalog_id = env("WB_CATALOG_ID");
        }
        $message = [
            "messaging_product"=> "whatsapp",
            "recipient_type"=>"individual",
            "to"=> $to ,
            "type"=> "interactive",
            "interactive"=> [
                "type"=> "product_list",
                
                "body"=> [
                    "text"=> $body_text
                ],
                "action"=> [
                    "catalog_id"=> $catalog_id,
                    "product_retailer_id"=>$products_id
                    
                    
                ]
            ]

        ];

        return json_encode($message);

    }
    
}