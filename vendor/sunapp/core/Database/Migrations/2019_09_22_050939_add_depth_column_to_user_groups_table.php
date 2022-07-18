<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SunAppModules\Core\Entities\UserGroup;
use SunAppModules\Core\src\Nestedset\NestedSet;

class AddDepthColumnToUserGroupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!(Schema::hasColumns('user_groups', [NestedSet::DEPTH]))) {
            Schema::table('user_groups', function (Blueprint $table) {
                $table->integer(NestedSet::DEPTH)->nullable()->after(NestedSet::RGT);
            });
            UserGroup::fixTree();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if ((Schema::hasColumns('user_groups', [NestedSet::DEPTH]))) {
            Schema::table('user_groups', function (Blueprint $table) {
                $table->dropColumn(NestedSet::DEPTH);
            });
        }
    }
}
