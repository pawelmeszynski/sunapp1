<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginAsUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('login_as_users')) {
            Schema::create('login_as_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_entity_from')->nullable();
                $table->bigInteger('user_id_from')->nullable()->unsigned()->index();
                $table->string('user_entity_to')->nullable();
                $table->bigInteger('user_id_to')->nullable()->unsigned()->index();
                $table->string('token')->index();
                $table->timestamp('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('login_as_users');
    }
}
