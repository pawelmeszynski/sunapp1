<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAndSyncColumnToCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sunbet_competitions', function (Blueprint $table) {
            $table->boolean('status')->default(false)->nullable()->after('name');
            $table->boolean('sync')->default(false)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sunbet_competitions', function (Blueprint $table) {

        });
    }
}
