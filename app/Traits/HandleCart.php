<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\CheckOutContact;
use App\Models\Product;
use Illuminate\Support\Facades\Config;

trait HandleCart
{
    use SendMessage, HandleSession, HandleCheckOutContact, HandleOrder, HandleEntities;

    /* 
    Cart codes 
    cart commands will have the format (cart:1)
    1 === check out
    2 === clear cart
    3 === continue shopping
    
    */


    public function cart_index($split_message = null, $amount = null, $extra_detail = null)
    {

        $cart_command = $split_message[0];
        $command_value = $split_message[1];
        if ($cart_command == "cart_add" || $cart_command == "cart_remove") {


            if ($amount == null || $extra_detail == null) {
                $new_command =  ["active" => true, "command" => $cart_command, "command_value" => $command_value];
                $this->add_command_to_session($new_command);
                $product = $this->fetch_product($command_value);
                if ($cart_command == "cart_remove") {
                    $text = <<<MSG
                    Please respond with the number of {$product->name} to be subtracted from cart
                    MSG;
                } else {
                    $text = <<<MSG
                    Please respond with the number of {$product->name} to be added to cart
                    MSG;
                }

                $this->send_text_message($text);
            } else {
                $amount = (int) $amount;
                if (!intval($amount)) {
                    $this->send_text_message("Please respond with a number!");
                }
                $product = $this->fetch_product($command_value);

                if ($cart_command == "cart_remove") {
                    $this->subtract_from_cart($command_value, $amount);
                    $text = <<<MSG
                    {$amount} {$product->name} has been removed from cart!
                    MSG;
                } else {
                    $this->add_item_to_cart($command_value, $amount);
                    $text = <<<MSG
                    {$amount} {$product->name} has been added to cart!
                    MSG;
                }
                $this->send_text_message($text);
            }
        }

        if ($cart_command == "cart_do") {
            switch ($command_value) {
                case 'clear':
                    $this->clear_cart();
                    $this->update_session();
                    $this->send_text_message("Your cart has been successfully cleared");
                    die;
                    break;
                case 'checkout':
                    $model = new Cart();
                    $fetch = $model->where('whatsapp_id', $this->userphone)->get();
                    $count = count($fetch);
                    if ($count < 1) {
                        $this->send_text_message("Cart Is Empty! Please Select An Item From The Main Menu");
                        $this->send_post_curl($this->make_main_menu_message($this->userphone));
                        die;
                    }

                    if ($this->get_checkout_contact()) {
                        if (isset($this->user_session_data['order_note'])) {
                            // $question = Config::get("cart.note");
                            // // $data ="";
                            // $new_command =  ["active" => true, "command" => "add_note", "command_value" => ""];
                            // $this->add_command_to_session($new_command);
                            // $this->send_text_message($question);
                            // die;
                            $this->send_text_message("Thank you.Your checkout is being prepared!");
                            $data = $this->create_new_order();
                            $link = $data['data']['link'];

                            $this->send_text_message("Your Order Has been Prepared! Please Use This Link To Make Payment And Complete Your Order! \n{$link}");
                            $this->update_session();
                            $this->clear_cart();
                            die;
                        } else {
                         
                            $question = Config::get("cart.note");
                            // // $data ="";
                            $new_command =  ["active" => true, "command" => "add_note", "command_value" => ""];
                            $this->add_command_to_session($new_command);
                            $this->send_text_message($question);
                            die;
                        }
                    }



                    break;
                case 'continue':
                    $text = "I have brought you back to our main menu,  
                    \nplease Go ahead and select from one or more of our product and services here.";
                    $data = $this->make_main_menu_message($this->userphone, $text);
                    $this->send_post_curl($data);
                    die;
                    break;

                default:
                    # code...
                    break;
            }
        }
    }


    public function add_item_to_cart($product_id, $amount, $product_type = "")
    {

        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('product_id', $product_id)
            ->first();
        $needs_extra = Config::get('cart.needs_extra');

        if (!$fetch) {
            $model->whatsapp_id = $this->userphone;
            $model->product_id = $product_id;
            $model->amount = $amount;
            $model->save();

            if (in_array($product_type, $needs_extra)) {
                $this->get_extra_info($product_type);
            }
        } else {
            $new_amount = $fetch->amount + $amount;
            $this->update_cart_item($product_id, $new_amount);
        }
    }

    public function add_wa_cart_items()
    {
        $items = $this->wa_cart;
        foreach ($items as $item) {

            $wa_item_id = $item['product_retailer_id'];
            $quantity = $item['quantity'];
            $product = $this->fetch_product("", $wa_item_id);
            $this->add_item_to_cart($product->id, $quantity, $product->product_type);
            // dd($product->product_type);


        }
        // dd($this->user_session_data);
        $this->get_cart();
        die;
    }
    public function subtract_from_cart($product_id, $amount)
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('product_id', $product_id)
            ->first();

        if (!$fetch) {
            $this->send_text_message("Item Not In Cart!");
            die;
        } else {
            $new_amount = $fetch->amount - $amount;
            if ($new_amount < 1) {
                $this->remove_item_from_cart($product_id);
            } else {
                $this->update_cart_item($product_id, $new_amount);
            }
        }
    }

    public function update_cart_item($product_id, $amount)
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('product_id', $product_id)
            ->update([
                "amount" => $amount
            ]);
    }
    public function remove_item_from_cart($product_id)
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->where('product_id', $product_id)
            ->delete();
    }

    public function get_cart()
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)->get();
        $count = count($fetch);
        if ($count < 1) {
            $this->send_text_message("Cart Is Empty! Please Select An Item From The Main Menu");
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
        } else {
            $product_model = new Product();
            $item_count = 0;
            $i = 0;
            $total_amount_cost = 0;
            $cart_text = '';
            foreach ($fetch as $key => $value) {
                $i++;
                $fetch_product = $product_model->where('id', $value->product_id)->first();
                $total_amount_cost += $fetch_product->price * $value->amount;
                $item_count += $value->amount;
                if($fetch_product->shipping_cost != 0) {
                    $total_amount_cost += $fetch_product->shipping_cost;
                    $shipping_cost = "shipping cost@ $".$fetch_product->shipping_cost;
                }else{
                    $shipping_cost = "";
                }
                $cart_text .= $i . ") " . $fetch_product->name . " x" . $value->amount . " price @ \${$fetch_product->price}" ." {$shipping_cost}"."\n";
            }
            $new_cart_text = <<<MSG
            Total Number Of Items   ---- {$item_count}

            Total Amount Cost       ---- \${$total_amount_cost}
            MSG;
            $cart_buttons = [
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "agreement",
                        "title" => "Check Out"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "cart_do:clear",
                        "title" => "Clear Cart"
                    ]
                ],
                [
                    "type" => "reply",
                    "reply" => [
                        "id" => "menu",
                        "title" => "Main Menu"
                    ]
                ]

            ];
            $final_text = $cart_text . "\n \n" . $new_cart_text;
            $post_data = $this->make_button_message($this->userphone, "Cart Details", $final_text, $cart_buttons);
            // $this->send_text_message($cart_text);
            $this->send_post_curl($post_data);
            die;
        }
    }
    public function clear_cart()
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->first();

        if (!$fetch) {
            $this->send_text_message("Cart Is Empty Already");
            die;
        }
        $fetch = $model->where('whatsapp_id', $this->userphone)
            ->delete();
    }

    public function fetch_display_product($categoty_id = "", $product_id = "")
    {
        $model = new Product();
        if ($product_id != "") {
            $fetch =  $model->where('product_type', $product_id)->get();
        } else {
            $fetch =  $model->where('category', $categoty_id)->get();
        }
        $count = count($fetch);
        if ($count < 1) {
            $this->send_text_message("Sorry this product category is not available.");
        } else {
            $products = [];
            foreach ($fetch as $key => $value) {
                $product = [
                    "product_retailer_id" => $value->meta_product_id
                ];
                array_push($products, $product);
            }


            $multi_product_message = $this->make_multi_product_message($this->userphone, "Items", "Please choose from our options.", $products, "");
            // dd($multi_product_message);
            $this->send_post_curl($multi_product_message);
        }
    }

    // public function fetch_display_product($categoty_id)
    // {
    //     $model = new Product();
    //     $fetch =  $model->where('category', $categoty_id)->get();
    //     $count = count($fetch);
    //     if ($count < 1) {
    //         $this->send_text_message("Sorry this product category is not available.");
    //     } else {
    //         foreach ($fetch as $key => $value) {
    //             $text = <<<MSG
    //             Product: {$value->name}

    //             Price: {$value->price}
    //             Description: {$value->description}
    //             More Info: {$value->link}
    //             MSG;
    //             $image = env("APP_URL")."storage/app/public/".$value->image;
    //             // $image = null;
    //             $buttons = [
    //                 [
    //                     "type" => "reply",
    //                     "reply" => [
    //                         "id" => "cart_add:{$value->id}",
    //                         "title" => "Add To Cart"
    //                     ]
    //                 ],
    //                 [
    //                     "type" => "reply",
    //                     "reply" => [
    //                         "id" => "cart_remove:{$value->id}",
    //                         "title" => "Remove From Cart"
    //                     ]
    //                 ],
    //                 [
    //                     "type" => "reply",
    //                     "reply" => [
    //                         "id" => "cart_buy:{$value->id}",
    //                         "title" => "Buy Now"
    //                     ]
    //                 ]



    //             ];
    //             $product = $this->make_product_message($this->userphone, $text, $buttons, $image);
    //             $this->send_post_curl($product);
    //         }
    //         die;
    //     }
    // }

    public function fetch_product($product_id = "", $wa_item_id = "")
    {
        $model = new Product();
        if ($wa_item_id != "") {
            $fetch =  $model->where('meta_product_id', $wa_item_id)->first();
        } else {
            $fetch =  $model->where('id', $product_id)->first();
        }
        if (!$fetch) {
            $this->send_text_message("An error occured product those not exist");
            die;
        } else {
            return $fetch;
        }
    }

    public function get_extra_info($product_id)
    {
        $questions = Config::get('cart.extra_info_needed.' . $product_id);
        // dd('cart.extra_info_needed.'.$product_id);
        foreach ($questions as $question) {
            $data = ['question' => $question, 'answer' => ''];
            $this->add_new_object_to_session("", $data);
        }
    }

    public function check_questions()
    {
        $user_session = $this->user_session_data;
        foreach ($user_session as $data) {
            if (is_array($data)) {
                if (isset($data['question'])) {
                    if ($data['answer'] == "") {
                        $command = ["active_command" => true, "command" => "answer_question", "command_value" => ""];
                        $this->add_command_to_session($command);
                        $this->send_text_message($data['question']);
                        die;
                    }
                }
            }
        }
        return true;
    }

    public function get_cart_for_payment()
    {
        $model = new Cart();
        $fetch = $model->where('whatsapp_id', $this->userphone)->get();
        $count = count($fetch);
        if ($count < 1) {
            $this->send_text_message("Cart Is Empty! Please Select An Item From The Main Menu");
            $this->send_post_curl($this->make_main_menu_message($this->userphone));
            die;
        } else {
            $product_model = new Product();
            $item_count = 0;
            $i = 0;
            
            if(isset($this->user_session_data['delivery_area']))
            {
    

            $total_amount_cost = 0;
            // $total_amount_cost += $user_session_data['delivery_area']['cost'];
            $extras = "Shipping Area@". $this->user_session_data['delivery_area']['name'] . "\n";
            


            }else{
            $extras = "";
            $total_amount_cost = 0;
            }
            $meeting_set = 0;

            $meeting_link = env("CALENDLY_LINK");
            $meeting_text = "";
            $cart_text = '';
            foreach ($fetch as $key => $value) {
                $i++;
                $fetch_product = $product_model->where('id', $value->product_id)->first();
                $total_amount_cost += $fetch_product->price * $value->amount;
                $item_count += $value->amount;
                if($fetch_product->shipping_cost != 0) {
                    $total_amount_cost += $fetch_product->shipping_cost;
                    $shipping_cost = "shipping cost@ $".$fetch_product->shipping_cost;
                }else{
                    $shipping_cost = "";
                }
                $cart_text .= $i . ") " . $fetch_product->name . " x" . $value->amount . " price @ \${$fetch_product->price}" ." {$shipping_cost}"."\n";
                
               
                if ($fetch_product->add_calendly == 1 && $meeting_set < 1) {
                    $meeting_set++;
                    $meeting_text = <<<MEET
                    Please now use the calendy function to book your discovery session to go over the brief. We look forward to our engagement.
                    {$meeting_link}
                    MEET;
                    if($fetch_product->ask_for_link == 1)
                    {
                        if(!isset($this->user_session_data['docs_links']) )
                        {
                            $command = ["active_command" => true, "command" => "send_link", "command_value" => ""];
                            $this->add_command_to_session($command);
                            if($fetch_product->product_type == 4)
                            {
                                $this->send_text_message("please send me a link to your content with examples of music, keywords and intentions for your sensory marketing.");
                            }else{
                                $this->send_text_message("Please send me a link to your content relating to your customer pinpoints before our session.");
                            }
                            
                            die;
                        }else{
                            $extras .= <<<MSG
                            Links: {$this->user_session_data['docs_links']}
                            MSG;
                        }
                        

                    }
                }

                if($fetch_product->ask_shipping_area ==1 && ! isset($this->user_session_data['delivery_area']) )
                {
                    $command = ["active_command" => true, "command" => "set_delivery_area", "command_value" => ""];
                    $this->add_command_to_session($command);
                    $this->send_text_message("please select your area for delivery from the menu below");
                    $this->send_delivery_area();
                    die;

                }
            }
            if($meeting_text != "" || $extras !="")
            {
                $data = <<<MSG

                {$meeting_text}
    
                {$extras}
                MSG;
                $this->add_new_object_to_session("extra_info",$data);
            }
            // dd($meeting_text,$extras);
            $new_cart_text = <<<MSG
            Total Number Of Items   ---- {$item_count}
            Total Amount Cost       ---- \${$total_amount_cost}
            MSG;

            $final_text = $cart_text . "\n \n" . $new_cart_text;
            // dd($final_text,$extras);

            return [$total_amount_cost, $final_text];
        }
    }
}
