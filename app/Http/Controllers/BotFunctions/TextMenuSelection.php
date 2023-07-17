<?php

namespace App\Http\Controllers\BotFunctions;

use App\Models\Questions;
use App\Traits\MessagesType;
use App\Traits\SendMessage;
use Illuminate\Support\Facades\Config;

class TextMenuSelection extends GeneralFunctions
{


    public $expected_responses = [];
    public $mapped_responses = [];
    public $menu_data;
    public $menu_as_text;
    public $item_menu_counter = 1;
    public $extra_data;



    public function __construct(mixed $menu_data, $extra_data = '')
    {
        parent::__construct();
        $this->extra_data = $extra_data;

        $this->menu_data = $menu_data;
        $this->make_menu_data();
    }



    public function send_menu_to_user($message = "")
    {
        // echo $this->user
        if ($message == "") {
            $message = "Please select an option from the menu";
        }

        $text = $this->make_text_message($message, $this->userphone);
        $send = $this->send_post_curl($text);
        $text = $this->make_text_message($this->menu_as_text, $this->userphone);
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


            array_push($this->expected_responses, $item['name']);
            $this->map_response_to_data($item['name'], $item['name']);
            array_push($this->expected_responses, $this->item_menu_counter);
            $this->map_response_to_data($item['name'], $this->item_menu_counter);
            $this->menu_as_text .= "{$this->item_menu_counter}. " . $item['name'] . "\n" . "\n";
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

    public function multiple_menu_message()
    {
        // $menu_messages = [["message","menu text"]...];
        // 
        $question_model = new Questions();
        $menu_messages = [];
        $intro_mesasges = Config::get("intro_messages");
        $this->item_menu_counter = 1;
        $specific_intro_messages = $intro_mesasges[$this->app_config_cred["category"]];
        // loop through the sub categories array to get keys and intro messages keys are to be used for also checking what sub cat a question
        // belongs to before creatin an array of intro message key pair and also with the menu text 
        foreach ($specific_intro_messages as $sub_category_id => $intro_message) {
            $menu_txt = "";                                                                                
            $questions = $question_model->where("category", $this->app_config_cred['category'])
            ->where("sub_category",$sub_category_id)->get();

            if($questions->count() > 0)
            {
                foreach ($questions as $question => $value) {
                    $menu_txt  .= "{$this->item_menu_counter}. " .  $value->questions  . "\n" . "\n";
                    $this->item_menu_counter++;
                }
                array_push($menu_messages, ["message" => $intro_message, "menu_text" => $menu_txt]);
            }
            
        }

        // loop through the menu message to display the messages 
        foreach ($menu_messages as $key => $value) {
            $text = $this->make_text_message($value['message'], $this->userphone);
            $send = $this->send_post_curl($text);
            $text = $this->make_text_message($value['menu_text'], $this->userphone);
            $send = $this->send_post_curl($text);
        }
    }

    public function check_selection_from_multiple_menu_message($response)
    {
        // $menu_messages = [["message","menu text"]...];
        // 
        $question_model = new Questions();
        $menu_messages = [];
        $intro_mesasges = Config::get("intro_messages");
        $this->item_menu_counter = 1;
        $specific_intro_messages = $intro_mesasges[$this->app_config_cred["category"]];
      /**
       * create an array from the list of questions that are correctly matched only with their category and sub cat  ID
       * then use the array to create an object that will be used to create a new object of this class then used to create expexted responses
       * that would be checked and responded to accordingly 
       */
        foreach ($specific_intro_messages as $sub_category_id => $intro_message) {                                                                           
            $questions = $question_model->where("category", $this->app_config_cred['category'])
            ->where("sub_category",$sub_category_id)->get();

            if($questions->count() > 0)
            {
                foreach ($questions as $question => $value) {
                    array_push($menu_messages, $value->questions);                   
                }
            }
            
        }

        $array_to_obj = $this->MenuArrayToObj($menu_messages);
        $new_obj = new TextMenuSelection($array_to_obj);
        $new_obj->make_menu_data();
        if (!in_array($response, $this->expected_responses))
        {
            $message = "Please select an option from the menu";
            $text = $this->make_text_message($message, $this->userphone);
            $send = $this->send_post_curl($text);

            $this->multiple_menu_message();
            $this->ResponsedWith200();
        }

        return true;

        
    }
}
