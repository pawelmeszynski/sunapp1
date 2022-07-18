<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeValuesColumnsSizeInAuditsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->longText('old_values')->change();
            $table->longText('new_values')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->text('old_values')->change();
            $table->text('new_values')->change();
        });
    }
}
