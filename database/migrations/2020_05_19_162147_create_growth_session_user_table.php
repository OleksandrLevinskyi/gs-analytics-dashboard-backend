<?php

use App\Models\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrowthSessionUserTable extends Migration
{
    public function up()
    {
        Schema::create('growth_session_user', function (Blueprint $table) {
            $table->foreignId('user_id')->cascadeOnDelete();
            $table->foreignId('growth_session_id')->cascadeOnDelete();
            $table->foreignId('user_type_id')->default(UserType::ATTENDEE_ID)->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('growth_session_user');
    }
}
