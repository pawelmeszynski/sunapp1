<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ext_id')->nullable()->unsigned()->index();
            $table->nestedSet();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('core')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_group_id')->nullable()->unsigned()->index();
            $table->bigInteger('user_id')->nullable()->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_group');
        Schema::dropIfExists('user_groups');
    }
}
