<?php

namespace App\Traits;

use App\Models\Answers;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Support\Facades\Config;

use Google\ApiCore\ApiException;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;

trait HandleText
{
    use HandleButton, SendMessage,CreateActionsSession, HandleDialogFlow;

    public $text_intent;

    public function text_index()
    {
        $this->find_text_intent();
        if ($this->text_intent == "greetings") {
            $this->send_greetings_message($this->userphone);
        }else{
            $text_intent = $this->init_dialogFlow_two();
            $answer = $this->fetch_answer($text_intent);
            $message_to_send = $this->splitMessage($answer);

            foreach ($message_to_send as $message ) {
                $data  = $this->make_text_message($message);
                $this->send_post_curl($data);
                sleep(3);
            }
            die;
        }
        
        

    }

    public function show_menu_message()
    {
    }


    public function find_text_intent()
    {
        $message = $this->user_message_lowered;

        $greetings = Config::get("text_intentions.greetings");
        $menu = Config::get("text_intentions.menu");
      
        if (in_array($message, $greetings)) {
            $this->text_intent = "greetings";
        } else{
            $this->text_intent = "menu";
        }
         
    }

    public function fetch_answer($intent)
    {
        $question_model = new Questions();
        $answer_model = new Answers();

        $question = $question_model->where('questions',$intent)->first();
        $answer = $answer_model->where('question_id',$question->id)->first();
        return $answer->answers;
    }

    public function splitMessage($text, $limit = 4096) {

        if (strlen($text) > $limit) {
            $parts = str_split($text, ceil(strlen($text) / 2));
            $part1 = $parts[0];
            $part2 = $parts[1];
        
           return [$part1,$part2];
        } else {
            return [$text];
        }
    }
    




    public function  runtest(array $data)
    {
        return $this->test_response($data);
    }
}
