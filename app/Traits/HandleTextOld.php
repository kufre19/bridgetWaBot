<?php

namespace App\Traits;

// use App\Http\Controllers\BotAbilities\Main;

use App\Http\Controllers\BotAbilities\GetInfo;
use App\Models\Answers;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\BotAbilities\QuestionCounter;
use Google\ApiCore\ApiException;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;

trait HandleTextOld
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
        } else {

            // this code here will first go to db to search for intent/question and pick answer it returns not found if not found in db or 
            // probably not umderstood then this will repeat again but using dialogflow to get intent/question first
            $answer = $this->fetch_answer($this->user_message_original);
            if ($answer != "not found") {
                $message_to_send = $this->splitMessage($answer);
                foreach ($message_to_send as $message) {
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    sleep(2);
                }
                $main = new QuestionCounter();
                $main->checkQsCount();
                $this->ResponsedWith200();
            } else {
                $text_intent = $this->init_dialogFlow_two();

                $answer = $this->fetch_answer($text_intent);


                if ($answer == "not found") {
                    $message = "Sorry I'm still learning I do not undertand your question";
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    $this->ResponsedWith200();
                }
                $message_to_send = $this->splitMessage($answer);

                foreach ($message_to_send as $message) {
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    sleep(3);
                }
                $main = new QuestionCounter();
                $main->checkQsCount();
                $this->ResponsedWith200();
            }
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

    public function splitMessage($text, $limit = 4096)
    {

        if (strlen($text) > $limit) {
            $parts = str_split($text, ceil(strlen($text) / 2));
            $part1 = $parts[0];
            $part2 = $parts[1];

            return [$part1, $part2];
        } else {
            return [$text];
        }
    }





    public function  runtest(array $data)
    {
        return $this->test_response($data);
    }
}
