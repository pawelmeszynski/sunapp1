<?php  // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityLocksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('security_locks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('blocked')->default(1);
            $table->ipAddress('ip_address');
            $table->dateTime('blocked_from');
            $table->dateTime('blocked_to');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('security_locks');
    }
}
