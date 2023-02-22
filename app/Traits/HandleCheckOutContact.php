<?php

namespace App\Traits;

use App\Models\CheckOutContact;

trait HandleCheckOutContact {
    use HandleSession, SendMessage;

    public function choc_index ($command,$response_from_user="")
    {

    }



    public function get_checkout_contact()
    {
        $model = new CheckOutContact();
        $fetch = $model->where("belongs_to",$this->userphone)->first();
        if(!$fetch)
        {
            $data = $this->create_new_choc();
            // dd($data);
            if($data)
            {
                return true;
            }
        }
        return true;
    }

    public function create_new_choc($command_value="",$response_from_user="")
    {
        $user_session = $this->user_session_data;

     
        if(!isset($user_session['choc_form']) )
        {
            $data = ["name"=>"","email"=>"","address"=>"","telephone_number"=>""];
            $this->add_new_object_to_session("choc_form",$data);

            $command = ["active_command"=>true,"command"=>"create_choc","command_value"=>"name"];
            $this->add_command_to_session($command);
            $text = <<<MSG
            Let's, Get Your Checkout Info First, Respond With The Correct Answers To The Following

            Your Name
            Your Surname
            Your Email
            Your Address
            Your Telephone Number
            Your Postal Code
            MSG;
            $this->send_text_message($text);
            sleep(3);
            $this->send_text_message("Please Enter Your Name and Surname");
            die;
            
        }else{
            $user_session = $this->user_session_data;
            $form_fields = $user_session['choc_form'];
            $user_session['choc_form'][$command_value] = $response_from_user;
            $this->update_session($user_session);
            // $this->add_command_to_session();//empty command
            // $this->send_text_message("Stop here ");

            $user_session = $this->user_session_data;
            $form_fields = $user_session['choc_form'];


            $form_questions = [
                "name"=>"Please enter your Name and Surname",
                "address"=>"please enter your address and postal code",
                "telephone_number"=>"please enter your phone number With country code",
                "email"=>"Please provide me with your email address that will be used to send you details about payment. "
            ];
    
    
    
            foreach($form_fields as $field=>$value)
            {
                if($value == "")
                {
                    $command = ["active_command"=>true,"command"=>"create_choc","command_value"=>$field];
                    $this->add_command_to_session($command);
                    $this->send_text_message($form_questions[$field]);
                    die;
                }
    
            }
    
            $model = new CheckOutContact();
            $model->name = $form_fields['name'];
            $model->address = $form_fields['address'];
            $model->telephone = $form_fields['telephone_number'];
            $model->email = $form_fields['email'];
            $model->belongs_to = $this->userphone;
            $model->save();

            $text  ="Thank you for submitting your contact details for me to process your order";
            $command = ["active_command"=>true,"command"=>"continuation","command_value"=>"cart_do:checkout"];
            $this->add_command_to_session($command);
            $this->remove_object_from_session("choc_form");
            $data = $this->send_text_message($text,$this->userphone,$text);
            // $this->send_post_curl($data);
           $this->continue_session_command();
            
        }

        

        
        
        
        
    }

}