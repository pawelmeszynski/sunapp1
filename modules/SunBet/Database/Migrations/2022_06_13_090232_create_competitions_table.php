<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\Database\Models;

class CreateCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sunbet_competitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('emblem')->nullable();
            $table->string('plan')->nullable();
            $table->timestamps();

            $table->foreign('area_id')->references('id')->on('sunbet_areas');
            $table->foreign('team_id')->references('id')->on('sunbet_teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sunbet_competitions');
    }
};
