<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('slug')->unique();
            $table->string('slug_str');
            $table->integer('slug_id')->unsigned();
            $table->string('name', 100);
            $table->string('description');

            $table->string('unitsystem');
            $table->decimal('cgpos',5,2);
            $table->decimal('weight',6,2);
            $table->boolean('drawvalues');
            $table->decimal('rootchord',7,2);
            $table->decimal('totalspan',7,2);

            $table->json('panels');

            $table->decimal('wingarea',7,2);
            $table->decimal('macdist',7,2);
            $table->decimal('maclength',7,2);
            $table->decimal('cg',7,2);
            $table->decimal('wingload',7,2);

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['slug_str', 'slug_id']);
            $table->index('slug_str');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('wings', function (Blueprint $table) {
        //     $table->dropForeign('wings_user_id_foreign');
        //     $table->dropColumn('user_id');
        // });
        Schema::drop('wings');
    }
}
