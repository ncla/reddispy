<?php

namespace App\Models\Reddit;

use Illuminate\Database\Eloquent\Model;

class PostHistory extends Model
{
    protected $table = 'posts_history';

    const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'score',
        'upvote_ratio',
        'view_count' .
        'created_at'
    ];
}
