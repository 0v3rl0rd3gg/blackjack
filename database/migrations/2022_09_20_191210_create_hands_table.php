<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hands', function (Blueprint $table) {
            $table->id();
	        $table->foreignId('user_id')->constrained('users');
            $table->string('card_1',3)->default(null);
            $table->string('card_2',3)->default(null);
            $table->string('card_3',3)->default(null)->nullable();
            $table->string('card_4',3)->default(null)->nullable();
            $table->string('card_5',3)->default(null)->nullable();
            $table->integer('hand_value')->default(0);
            $table->boolean('is_split')->default(false)->nullable();
            $table->boolean('is_double_down')->default(false)->nullable();
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
        Schema::dropIfExists('hands');
    }
};
