<?php

namespace App\Models\Reddit;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'reddit_id',
        'title',
        'post_text',
        'submitted_at'
    ];

    public function history()
    {

    }
}
