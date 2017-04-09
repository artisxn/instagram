<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstagramCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pk')->index();
            $table->string('post_pk')->index();
            $table->string('user_pk')->index();
            $table->text('text');
            $table->timestamp('created_at_utc');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('instagram_comments');
    }
}
