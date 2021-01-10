<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'user_id',
        'claimable_type',
        'claimable_id',
        'remote_id',
        'token',
        'coin_reward',
        'accepted'
    ];

    /**
     * @return string
     */
    public static function generateToken()
    {
        $prefix = env('CLICK_TOKEN_PREFIX', 'wintale');
        $token = $prefix . '-' . make_random_hash($prefix);
        $click = Claim::where('token', $token)->first();
        if ($click) {
            return static::generateToken();
        }
        return $token;
    }
}
