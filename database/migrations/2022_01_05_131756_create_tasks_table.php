<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('end_date');
            $table->string('end_time');
            $table->string('picture');
            $table->unsignedBigInteger('from_user');
            $table->foreign('from_user')->references('id')->on('users');
            $table->unsignedBigInteger('to_user');
            $table->foreign('to_user')->references('id')->on('users');
            $table->string('task_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
