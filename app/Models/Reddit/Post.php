<?php

namespace App\Models\Reddit;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $timestamps = true;

    protected $table = 'posts';

    protected $fillable = [
        'reddit_id',
        'title',
        'post_text',
        'subreddit_id',
        'subreddit_name',
        'submitted_at'
    ];

    public function history()
    {
        return $this->hasMany(PostHistory::class, 'post_reddit_id', 'reddit_id');
    }
}
