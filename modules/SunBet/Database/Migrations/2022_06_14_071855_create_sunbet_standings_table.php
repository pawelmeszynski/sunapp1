<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSunbetStandingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sunbet_standings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stage')->nullable();
            $table->string('type')->nullable();
            $table->string('group')->nullable();
            $table->unsignedBigInteger('competition_id')->nullable();
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
        Schema::dropIfExists('sumbet_standings');
    }
}

;
