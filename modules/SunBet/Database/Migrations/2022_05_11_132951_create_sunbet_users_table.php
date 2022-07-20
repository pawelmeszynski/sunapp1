<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSunbetUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sunbet_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->integer('points')->default(0);
            $table->boolean('is_ldap')->default(0);
            $table->string('ldap_name')->nullable();
            $table->rememberToken();
            $table->dateTime('logged_at')
                ->nullable()
                ->default(null);
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
        Schema::dropIfExists('sunbet_users');
    }
}

;
