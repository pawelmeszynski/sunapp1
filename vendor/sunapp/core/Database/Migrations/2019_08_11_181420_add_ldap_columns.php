<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLdapColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_ldap')->after('api_token');
            $table->string('ldap_name')->after('is_ldap');
            $table->dateTime('logged_at')->after('remember_token')
                                        ->nullable()
                                        ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_ldap');
            $table->dropColumn('ldap_name');
            $table->dropColumn('logged_at');
        });
    }
}
