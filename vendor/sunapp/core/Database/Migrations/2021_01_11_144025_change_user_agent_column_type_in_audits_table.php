<?php  // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserAgentColumnTypeInAuditsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumns('audits', ['user_agent'])) {
            Schema::table('audits', function (Blueprint $table) {
                $table->text('user_agent')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumns('audits', ['user_agent'])) {
            Schema::table('audits', function (Blueprint $table) {
                $table->string('user_agent')->nullable()->change();
            });
        }
    }
}
