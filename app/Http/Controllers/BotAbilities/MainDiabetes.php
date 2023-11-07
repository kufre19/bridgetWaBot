<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\Answers;
use App\Models\Questions;
use App\Models\ScheduleMenu;
use App\Models\User;
use Illuminate\Http\Request;

class MainDiabetes extends BotFunctionsGeneralFunctions implements AbilityInterface
{
    public $steps = ["begin_func", "sendAnswers"];
    public $accepted_terms;
    public $question_arr_list = [];

    public function begin_func()
    {
        $this->get_Wa_user();

        if ($this->accepted_terms == "accepted") {
            // send intro and list of questions
            $this->set_session_route("MainDiabetes");
            $this->go_to_next_step();
            $this->continue_session_step();
        } else {
            $terms_andcondition = new TermsAndCondition();
            $terms_andcondition->begin_func();
        }
    }

    public function sendAnswers()
    {
        // check for progress first
        // if question_asked is empty then user just started then send first question to user and expect a response of one
        // if it's not empty then check user sent response if can be found in question asked list
        // and then in table as corresponding_number for question if found in the previous list
        // if not found check as question 
        // if found store the value of corresponding number and
        // increement the value for corresponding number asked by 1 then store

        $question_progress = $this->getQuestionProgress();
        if (!empty($question_progress['questions_asked'])) {
            // user did not just start conversation

            if (in_array($this->user_message_original, $question_progress['questions_asked'])) {

                // user asked/ pressed authorized question/number
                // make method to check and fetch the question
                // method to upodate the corressponding_number and store it
                // send the answer here
                $answer = $this->getAnswer($this->user_message_original);
                if ($answer == false) {
                    $this->ResponsedWith200();
                }
                $text = $this->make_text_message($answer, $this->userphone);
                $send = $this->send_post_curl($text);
                $this->ResponsedWith200();
            } else {
                // user response not authorized might just kill the flow here
                $this->ResponsedWith200();
            }
        } else {
            // user just started conversation
            $question_progress['questions_asked'][] = 1;
            $new_session = $this->user_session_data;
            $new_session['question_progress']['diabetes'] = $question_progress;
            $this->update_session($new_session);

            $message = "Press 1 to get started with your learning journey!";
            $text = $this->make_text_message($message, $this->userphone);
            $send = $this->send_post_curl($text);
            $this->ResponsedWith200();
        }
    }

    public function getAnswer($corresponding_number)
    {
        $question = Questions::where("category", $this->app_config_cred['category'])->where("corresponding_number", $corresponding_number)->first();

        if (!$question) {
            // not found the question
            return false;
        } else {
            // update the question progress
            // fetch answer from queston
            // return the answer

            $this->updateQuestionProgress($question);
            $answer = Answers::where("question_id", $question->id)->first();
            $the_question = $question->questions;
            $ans = <<<MSG
            $the_question

            $answer->answers
            MSG;
            return  $ans;
        }
    }

    public function updateQuestionProgress($question)
    {
        $question_progress = $this->user_session_data['question_progress']['diabetes'];
        if (!in_array($this->user_message_original, $question_progress['questions_asked']))
        {
            $old_corresponding = $question->corresponding_number;
            $new_corresponding = $old_corresponding + 1;
            $question_progress['questions_asked'][] = $new_corresponding;
    
            $new_session = $this->user_session_data;
            $new_session['question_progress']['diabetes'] = $question_progress;
            $this->update_session($new_session);
        }
       
    }

    public function getQuestionProgress()
    {
        $question_progress = $this->user_session_data['question_progress']['diabetes'] ?? "";
        // createa the object if it's empty
        if ($question_progress == "") {
            $question_progress = ["diabetes" => [
                "category" => $this->app_config_cred['category'],
                "sub_category" => 1,
                "sub_cat_limit" => $this->app_config_cred['no_of_sub_cat'],
                "questions_asked" => [],
                "sub_cats_finished" => [],
            ]];
            $this->add_new_object_to_session("question_progress", $question_progress);
            $question_progress = $question_progress['diabetes'];
        }

        return $question_progress;
    }
    public function get_Wa_user()
    {
        $usermodel = new User();
        $user = $usermodel->where('whatsapp_id', $this->userphone)
        ->where('bot_category', $this->app_config_cred['category'])
        ->first();

        $this->accepted_terms = $user->accepted_terms;
    }

    public function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }
}
