<?php

namespace App\Http\Controllers\BotAbilities;

use App\Http\Controllers\BotFunctions\GeneralFunctions as BotFunctionsGeneralFunctions;
use App\Http\Controllers\BotFunctions\TextMenuSelection;
use App\Models\ScheduleMenu;
use Illuminate\Http\Request;


class QuestionCounter extends BotFunctionsGeneralFunctions implements AbilityInterface
{

    public $steps = ["checkQsCount", "qsFlow", "test_main3"];
    public const QS_COUNT = "qs_count";



    public function begin_func()
    {
        // sets new route to this class
        // and head to another method to ask for approval to begin flow
        $this->set_session_route("QuestionCounter");
        $this->go_to_next_step();
        $this->continue_session_step();
    }

    public function qsFlow()
    {
        $form_counter = $this->user_session_data['form_counter'];
        $ask_qs = false;
        $skip = false;
        $repeat_last_action = false;

        switch ($form_counter) {
            case '0':


                $qs = <<<MSG
                Well done! You have improved your knowledge about diabetes or hypertension. 
                I appreciate your efforts. How do you want to continue your journey to beat diabetes or 
                hypertension? Can we plan together to beat diabetes or hypertension?
                MSG;
                $header = "Improved your knowledge";
                $btn = [
                    [
                        "type" => "reply",
                        "reply" => [
                            "id" => "yes",
                            "title" => "Yes"
                        ]
                    ],
                    [
                        "type" => "reply",
                        "reply" => [
                            "id" => "no",
                            "title" => "No"
                        ]
                    ],
                ];
                $this->message_user_btn($qs, $this->userphone, $header, $btn);
                $ask_qs = true;
                break;

            case '1':
                // check if user responded with btn yes/no or text y/n if not any of it repeat the last one
                $user_response = $this->button_id ?? $this->user_message_lowered;
                if ($user_response == "yes") {
                    $message =  <<<MSG
                    Thank you for working with me to beat these risk factors? What concerns do 
                    you have with your blood sugar or blood pressure readings?
                    MSG;
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    $ask_qs = true;
                }
                elseif ($user_response == "no") {
                    $message =  <<<MSG
                    Thank you. I understand that you are not ready to plan yet. Will you like to keep learning? 
                    Share your concerns with me by asking a question
                    MSG;
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    $this->update_session();
                    $this->ResponsedWith200();
                }else{
                    $message =  <<<MSG
                    sorry i did not understand the response please can you repeat that again
                    MSG;
                    $data  = $this->make_text_message($message);
                    $this->send_post_curl($data);
                    $repeat_last_action = true;
                }

                
                break;

            case '2':
                $message =  <<<MSG
                I appreciate your bravery to share your concerns with me today. What actions do you want to take 
                to improve your blood sugar and/or blood pressure readings?
                MSG;
                $data  = $this->make_text_message($message);
                $this->send_post_curl($data);
                $ask_qs = true;
                break;

            case '3':
                $message = <<<MSG
                Thank you for choosing to work with me to address your concerns about diabetes or hypertension today. I understand 
                that you want to make changes to improve your wellbeing.
                MSG;
                $data  = $this->make_text_message($message);
                $this->send_post_curl($data);
                break;

            case '4':
                $message = <<<MSG
                Lifestyle changes that keep blood pressure or blood sugar within normal limits involve healthy diets, increased physical activity, weight management, 
                stress management and use of medications. You need all of these to achieve normal readings. 
                Can I share ideas on how you can achieve these lifestyle changes?
                MSG;
                $data  = $this->make_text_message($message);
                $this->send_post_curl($data);
                $ask_qs = true;
                break;
        }

        if ($form_counter == 4) {
            $message = <<<MSG
            Thank you for taking this great step today. I will send you tips on how to 
            achieve these lifestyle changes.  
            You can keep asking more questions about these risk factors
            MSG;
            $data  = $this->make_text_message($message);
            $this->send_post_curl($data);
            $this->update_session();
            $this->ResponsedWith200();
        } else {

            if($repeat_last_action)
            {
                $this->go_to_previous_step_on_form();
                $this->continue_session_step();

            }else {
                $this->go_to_next_step_on_form();

            }

            if ($ask_qs == true) {
                $this->ResponsedWith200();
            } else {
                $this->continue_session_step();
            }
        }
    }



    public function checkQsCount()
    {
        // this should fetch user sesssion and check for a qs_count object/key in the session if not found create new one and
        // start count from 1

        $user_session = $this->user_session_data;
        if (isset($user_session[self::QS_COUNT])) {
            // check if it's up to five
            $counter = $user_session[self::QS_COUNT];
            if ($counter == 4) {
                // then start route to ask questions

                $this->begin_func();
            } else {
                $counter++;
                $this->add_new_object_to_session(self::QS_COUNT, $counter);
                $this->ResponsedWith200();
            }
        } else {
            $this->add_new_object_to_session(self::QS_COUNT, 0);
            $this->ResponsedWith200();
        }
    }





    function call_method($key)
    {
        $method_name = $this->steps[$key];
        $this->$method_name();
    }
}
