<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe_new_user(Request $request)
    {
        $name = $request->input("name");
        $phone = $request->input("phone");
        $bot_type = $request->input("bot_type");

        $exists = User::where("whatsapp_id",$phone)->where("bot_category",$bot_type)->exists();
        if(!$exists)
        {
            $model = new User();
            $model->name =$name;
            $model->whatsapp_id = $phone;
            $model->bot_category = $bot_type;
            $model->subscription = "active";
            $model->save();
            return response("user registered");

        }else {
            return response("user found");

        }

    }
}
