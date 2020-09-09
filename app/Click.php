<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'offer_id',
        'token'
    ];

    /**
     * @return string
     */
    public static function generateToken()
    {
        $prefix = env('CLICK_TOKEN_PREFIX', 'WinTale');
        $token = $prefix . '-' . make_random_hash($prefix);
        $click = Click::where('token', $token)->first();
        if ($click) {
            return static::generateToken();
        }
        return $token;
    }


}
