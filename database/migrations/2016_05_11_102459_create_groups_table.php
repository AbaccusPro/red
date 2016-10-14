<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('timeline_id')->unsigned();
            $table->enum('type', ['open', 'closed', 'secret']);
            $table->boolean('active')->default(1);
            $table->string('member_privacy', 200);
            $table->string('post_privacy', 200);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('groups');
    }
}
