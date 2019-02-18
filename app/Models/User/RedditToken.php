<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RedditToken extends Model
{
    protected $table = 'tokens';

    protected $fillable = [
        'user_id',
        'refresh_token',
        'access_token',
        'token_expires_in_seconds',
        'expires_at'
    ];

    protected $attributes = [
        'token_expires_in_seconds' => 3600
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
