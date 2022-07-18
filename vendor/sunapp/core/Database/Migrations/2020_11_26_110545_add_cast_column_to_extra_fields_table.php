<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCastColumnToExtraFieldsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('extra_fields', function (Blueprint $table) {
            $table->string('cast')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('extra_fields', function (Blueprint $table) {
            $table->dropColumn('cast');
        });
    }
}
