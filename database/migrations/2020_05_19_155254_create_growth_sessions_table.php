<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrowthSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('growth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('attendee_limit')->default(PHP_INT_MAX);
            $table->string('discord_channel_id')->nullable(true)->after('location');
            $table->boolean('is_public')->default(false)->after('end_time');
            $table->text('topic');
            $table->string('location');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('growth_sessions');
    }
}
