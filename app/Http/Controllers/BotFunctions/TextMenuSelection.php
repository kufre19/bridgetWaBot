<?php

namespace App\Http\Controllers\BotFunctions;

use App\Traits\MessagesType;
use App\Traits\SendMessage;

class TextMenuSelection extends GeneralFunctions
{


    public $expected_responses = [];
    public $mapped_responses = [];
    public $menu_data;
    public $menu_as_text;
    public $item_menu_counter = 1;
    public $extra_data ;



    public function __construct(mixed $menu_data,$extra_data='')
    {
        parent::__construct();
        $this->extra_data = $extra_data;

        $this->menu_data = $menu_data;
        $this->make_menu_data();
        
    }


    
    public function send_menu_to_user($message ="")
    {
         // echo $this->user
         if($message == "")
         {
             $message = "Please select an option from the menu";
         }
        
        $text = $this->make_text_message($message,$this->userphone);
        $send = $this->send_post_curl($text);
        $text = $this->make_text_message($this->menu_as_text,$this->userphone);
        $send = $this->send_post_curl($text);
    }

    /**
     * this method loops through an object mapping 
     * 1. first the text to each other to make sure if same text is sent it's found in the expeted responses array by pushing
     * data from the obj menu to the array
     * 2. then will map that response to the same data
     * 3. will now push the counter as expected response and then map the counter to the data
     * 
     */
    public function make_menu_data()
    {
      
        foreach ($this->menu_data as $item) {
           

            array_push($this->expected_responses,$item['name']);
            $this->map_response_to_data($item['name'],$item['name']);
            array_push($this->expected_responses,$this->item_menu_counter);
            $this->map_response_to_data($item['name'],$this->item_menu_counter);
            $this->menu_as_text .="{$this->item_menu_counter }. ". $item['name']. "\n". "\n";
            // should come last
            $this->item_menu_counter++;

        }
    
    }

    public function map_response_to_data($data, $response)
    {
        $this->mapped_responses[$response] = $data;
        return true;
    }

    public function check_expected_response($response)
    {
        if (!in_array($response, $this->expected_responses)) {
            info($response);
            $message = "Please select from the menu given!";
            $this->send_menu_to_user($message);
            return $this->ResponsedWith200();
        }

        return true;
    }

    public function get_selected_item($response)
    {
        $selected_item = $this->mapped_responses[$response];
        return $selected_item;
    }
}
