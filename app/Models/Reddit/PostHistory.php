<?php

namespace App\Models\Reddit;

use Illuminate\Database\Eloquent\Model;

class PostHistory extends Model
{
    const UPDATED_AT = null;

    protected $table = 'posts_history';

    protected $fillable = [
        'post_reddit_id',
        'score',
        'upvote_ratio',
        'view_count' .
        'created_at'
    ];
}
