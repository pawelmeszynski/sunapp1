<?php  // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('security_exceptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status_code')->nullable();
            $table->string('exception_type')->nullable();
            $table->ipAddress('ip_address');
            $table->text('url')->nullable();
            $table->longText('message')->nullable();
            $table->string('method')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('security_exceptions');
    }
}
