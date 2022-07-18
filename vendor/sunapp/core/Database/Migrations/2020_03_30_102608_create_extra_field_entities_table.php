<?php // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraFieldEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('extra_field_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extra_field_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('extra_field_entities');
    }
}
