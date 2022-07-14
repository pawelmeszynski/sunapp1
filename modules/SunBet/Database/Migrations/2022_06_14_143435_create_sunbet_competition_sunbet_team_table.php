<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSunbetCompetitionSunbetTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sunbet_competition_sunbet_team', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sunbet_team_id')->nullable();
            $table->foreign('sunbet_team_id')->references('id')->on('sunbet_teams');

            $table->unsignedBigInteger('sunbet_competition_id')->nullable();
            $table->foreign('sunbet_competition_id')->references('id')->on('sunbet_competitions');

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
        Schema::dropIfExists('sunbet_competition_team');
    }
};
