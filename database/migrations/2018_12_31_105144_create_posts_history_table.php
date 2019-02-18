<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts_history', function (Blueprint $table) {
            $table->increments('id');
            $table->string('post_reddit_id');
            $table->integer('score')->nullable();
            $table->float('upvote_ratio')->nullable();
            $table->integer('view_count')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts_history');
    }
}
