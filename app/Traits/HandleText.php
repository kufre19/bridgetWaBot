<?php

namespace App\Traits;

// use App\Http\Controllers\BotAbilities\Main;

use App\Http\Controllers\BotAbilities\GetInfo;
use App\Http\Controllers\BotAbilities\Main;
use App\Models\Answers;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\BotAbilities\QuestionCounter;
use Google\ApiCore\ApiException;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;

trait HandleText
{
    use HandleButton, SendMessage, CreateActionsSession, HandleDialogFlow;

    public $text_intent;

    public function text_index()
    {
        $this->find_text_intent();
        if ($this->text_intent == "run_action_steps") {
            $this->continue_session_step();
        }
        
        if ($this->text_intent == "greetings") {
           
            $this->send_greetings_message($this->userphone);

              // this will lead always to the main ability
              $main = new Main;
              $main->begin_func();
        } else {

            // this will lead always to the main ability
            $main = new Main;
            $main->begin_func();
         
        }
    }

   
    public function find_text_intent()
    {

        $message = $this->user_message_lowered;

        $greetings = Config::get("text_intentions.greetings");
        $menu = Config::get("text_intentions.menu");

        if (in_array($message, $greetings)) {
            $this->text_intent = "greetings";
        } elseif (isset($this->user_session_data['run_action_step'])) {
            if ($this->user_session_data['run_action_step'] == 1) {
                $this->text_intent = "run_action_steps";
            }
        } else {
            $this->text_intent = "menu";
        }
    }

    public function fetch_answer($intent)
    {
        $question_model = new Questions();
        $answer_model = new Answers();


        $question = $question_model->where('questions', $intent)->first();
        if (!$question) {
            // means not able to search question in db from user request so must use AI to get intent and question
            return "not found";
        }
        $answer = $answer_model->where('question_id', $question->id)->first();
        return $answer->answers;
    }

   




    public function  runtest(array $data)
    {
        return $this->test_response($data);
    }
}
