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

    public function multiple_menu_message($question_progress)
    {
        // firstly fetch the questions based on the question progress provided
        $question_model = new Questions();
        $questions = $question_model->where("category", $question_progress['category'])->where("sub_category",$question_progress['sub_category'])->get();

        $intro_mesasges = Config::get("intro_messages");
        $this->item_menu_counter = 1;
        $the_intro_message = $intro_mesasges[$question_progress['category']][$question_progress['sub_category']];
        $menu_text = "";
        $this->item_menu_counter = 1;

        if($questions->count() > 0)
        {
            foreach ($questions as $key => $value) {
                $menu_text .= "{$this->item_menu_counter}. ". $value->questions . "\n". "\n" ;
                $this->item_menu_counter++;
    
            }
        }
       

           
        $text = $this->make_text_message($the_intro_message, $this->userphone);
        $send = $this->send_post_curl($text);

        $text = $this->make_text_message($menu_text, $this->userphone);
        $send = $this->send_post_curl($text);

    }
   

    public function check_selection_from_multiple_menu_message($response,$question_progress)
    {
      
        // 
         // firstly fetch the questions based on the question progress provided
         $question_model = new Questions();
         $questions = $question_model->where("category", $question_progress['category'])->where("sub_category",$question_progress['sub_category'])->get();
 
         $intro_mesasges = Config::get("intro_messages");
         $this->item_menu_counter = 1;
         $the_intro_message = $intro_mesasges[$question_progress['category']][$question_progress['sub_category']];
         $menu_text = "";
         $this->item_menu_counter = 1;
         $menu_messages = [];

 
         if($questions->count() > 0)
         {
             foreach ($questions as $question => $value) {
                 array_push($menu_messages, $value->questions);
                 $menu_text .= "{$this->item_menu_counter}. ". $value->questions . "\n". "\n" ;
                 $this->item_menu_counter++;
     
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

            $this->multiple_menu_message($question_progress);
            $this->ResponsedWith200();
        }

        $selected = $new_obj->get_selected_item($response);

        // update question progress variable 
        // the values that must be tracked
        $question_asked = $question_progress['questions_asked'];
        $sub_category = $question_progress['sub_category'];
        $sub_cats_finished = $question_progress['sub_cats_finished'];


        // check the same question has been asked and recorded
        if(!in_array($selected,$question_asked)){
            array_push($question_asked,$selected);
        }
        // check if the length of the question asked has supassed the lenght of available questions
        if($questions->count() == count($question_asked))
        {
            // increase sub cat then check if it's passed its availabiltity before storing
            $sub_category++;
            if($sub_category > $question_progress['sub_cat_limit'])
            {
                return $selected;

            }else{
                array_push($sub_cats_finished,$sub_category);
                if($sub_category == $question_progress['sub_cat_limit'])
                {
                    $question_progress = [
                        "category"=>$this->app_config_cred['category'],
                        "sub_category"=>$sub_category,
                        "sub_cat_limit"=>$question_progress['sub_cat_limit'],
                        "questions_asked"=>$question_asked,
                        "sub_cats_finished"=>$sub_cats_finished,
                    ];
            
                    $this->add_new_object_to_session("question_progress",$question_progress);
    
                    return "next_sub";
                }

               

            }
        }
        $question_progress = [
            "category"=>$this->app_config_cred['category'],
            "sub_category"=>$sub_category,
            "sub_cat_limit"=>$question_progress['sub_cat_limit'],
            "questions_asked"=>$question_asked,
            "sub_cats_finished"=>$sub_cats_finished,
        ];

        $this->add_new_object_to_session("question_progress",$question_progress);


       return $selected;
        
    }
}
