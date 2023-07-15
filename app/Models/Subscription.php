<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id"

    ];

    public static function getUserSub($user_id)
    {
        $sub = self::where("user_id",$user_id)->first();
        if($sub)
        {
            return $sub;
        }
      return false ;

    }
}
