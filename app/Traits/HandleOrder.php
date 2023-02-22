<?php

namespace App\Traits;

use App\Models\CheckOutContact;
use App\Models\Order;
use Illuminate\Support\Str;

trait HandleOrder{
    use SendMessage;

    /* 
    payment status for orders include
        #not paid
        #paid
        #canceled
        #awaiting payment
        #in_transit
        #delivered
    */
   

    public function handle_order_index($split_message = null)
    {
        $order_command = $split_message[0];
        $command_value = $split_message[1];
        
        if($command_value == "menu")
        {
            $menu = [
                [
                    "id"=> "order_show:awaiting payment",
                    "title"=> "Awaiting",
                    "description"=> "See Orders with status awaiting"
                ],
                [
                    "id"=> "order_show:paid",
                    "title"=> "Paid",
                    "description"=> "See Orders with status paid"
                ],
                [
                    "id"=> "order_show:canceled",
                    "title"=> "Canceled",
                    "description"=> "See Orders with status canceled"
                ],
                [
                    "id"=> "order_show:in_transit",
                    "title"=> "In Transit",
                    "description"=> "See Orders with status in transit"
                ],
                [
                    "id"=> "order_show:delivered",
                    "title"=> "Delivered",
                    "description"=> "See Orders with status delivered"
                ]
              
            ];
            $data = $this->make_menu_message($this->userphone,$menu,"Orders","Orders Menu");
            $this->send_text_message("Please select a category of order you'd like to see from the menu below!");
            $this->send_post_curl($data);
            die;

        }
        if($order_command == "order_show")
        {
            $this->get_and_display_orders($command_value);

        }
        if($order_command == "order_fetch")
        {
            
            $order_info = $this->fetch_order($command_value);
            $this->send_text_message($order_info);
            // $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;

        }

    }

    public function fetch_order($order_id="",$tx_ref="")
    {
        $model = new Order();
        
        if($order_id =="")
        {
            $fetch = $model->where('tx_ref',$tx_ref)->first();
        }else{
            $fetch = $model->where('id',$order_id)->first();
        }
        if(!$fetch)
        {
            $this->send_text_message("Sorry there's an error with the order selected please contact support");
        }
        
        $show_all_details_for = ['paid',"in_transit","delivered"];
        $order_info = "";
        if(in_array($fetch->status,$show_all_details_for))
        {
            $extra = ($fetch->extra_info != "")  ? $fetch->extra_info:"No additional information available";
            // $extra = $fetch->extra_info ?? "not added";
            $order_info =<<<MSG
            ORDER ID: {$fetch->tx_ref}

            ORDER DATE: {$fetch->created_at}

            ORDER STATUS: {$fetch->status}

            ORDER NOTE: {$fetch->note}
            
            
            Additional information relating to your order:
            {$extra}

            MSG;

        }else {
            $order_info =<<<MSG
            ORDER ID: {$fetch->tx_ref}

            ORDER DATE: {$fetch->created_at}
            
            ORDER STATUS: {$fetch->status}


            ORDER NOTE:
            {$fetch->note}

            ORDER DETAILS: 
            {$fetch->details}

            Payment Link:{$fetch->payment_link}
            MSG;
        }
       
        return $order_info;

    }

    public function get_and_display_orders($status)
    {
        $model = new Order();
        $fetch =  $model->where('user_id', $this->userphone)->where("status",$status)->orderBy("created_at","desc")->get();

        $count = count($fetch);
        if ($count < 1) {
            $this->send_text_message("You have no order with status {$status} available!.");
            $data = $this->make_main_menu_message($this->userphone,"Go ahead and select from our menu and place an order");
            $this->send_post_curl($data);
            die;
        } else {
           
            $counter = 0;
            foreach ($fetch as $key => $value) {
                $counter++;
                $button = [
                    [
                        "type" => "reply",
                        "reply" => [
                            "id" => "order_fetch:{$value->id}",
                            "title" => "More Info"
                        ]
                    ]
                ];
                $header_message = "Order #{$counter}";
                $body_text = <<<MSG
                ORDER ID: {$value->tx_ref}
                ORDER DATE: {$value->created_at}
                MSG;
                $data = $this->make_button_message($this->userphone,$header_message,$body_text,$button);
                $this->send_post_curl($data);
               
            }
            die;

            
        }
    }


    public function create_new_order()
    {

        $order_model = new Order();
        $contact_model = new CheckOutContact();
        $cart = $this->get_cart_for_payment();
        $contact = $contact_model->where('belongs_to',$this->userphone)->first();
        $order_note = $this->user_session_data['order_note'] ?? "";
        $extra_info =  $this->user_session_data['extra_info'] ?? "" ;
        // dd($extra_info,$order_note);
        $this->add_command_to_session();

        $tx_ref = $this->new_id();

        $payload = [
            "tx_ref"=> $tx_ref,
            "amount"=>$cart[0],
            "currency"=>env("FLW_CURRENCY"),
            "redirect_url"=>env("FLW_REDIRECT"),
            "meta"=>["customer_id"=>$this->userphone],
            "customer"=>[
                "email"=>$contact->email,
                "phonenumber"=>$contact->telephone_number,
                "name"=>$contact->name." ".$contact->surname

            ],
      
        ];

        

        $order_model->tx_ref = $tx_ref;
        $order_model->status = "awaiting payment";
        $order_model->amount = $cart[0];
        $order_model->user_id = $this->userphone;
        $order_model->details = $cart[1];
        $order_model->note = $order_note;
        $order_model->payment_status ="awaiting payment";
        $order_model->extra_info = $extra_info;
        $order_model->tracking_id = "Not Available";
        $order_model->save();
        return $this->send_post_payment($payload, $tx_ref);

        

        
    }
    public function send_post_payment($post_data, $tx_ref)
    {
        $token = env("FLW_SECRET_KEY");
        $url = env("FLW_API");
        $post_data = json_encode($post_data);

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            "Authorization: Bearer {$token}"
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response,true);
        if($data['status'] =="success")
        {
            $this->save_payment_link($data['data']['link'],$tx_ref);
            return  $data;

        }else{
            $this->send_text_message("Sorry There's An Issue with the payment processor, support will be notified!");
            die;
        }
        

    }

    public function new_id()
    {
        return (string) Str::orderedUuid();
    }

    public function save_payment_link($link,$for)
    {
        $order_model = new Order();
        $order_model->where('tx_ref',$for)->update([
            "payment_link"=>$link
        ]);

    }




}