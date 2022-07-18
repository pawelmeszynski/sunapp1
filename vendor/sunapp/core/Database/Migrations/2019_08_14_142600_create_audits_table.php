<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('audits')) {
            Schema::create('audits', function (Blueprint $table) {
                $table->engine = 'MyISAM';
                $table->increments('id');
                $table->string('user_type', 240)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event');

                //$table->morphs('auditable');
                $table->string("auditable_type", 240);
                $table->unsignedBigInteger("auditable_id");
                $table->index(["auditable_type", "auditable_id"]);

                $table->text('old_values')->nullable();
                $table->text('new_values')->nullable();
                $table->text('url')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('tags')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'user_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('audits');
    }
}
