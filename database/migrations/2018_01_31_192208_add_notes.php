<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('events', function(Blueprint $table) {
            $table->string('VenueName')->nullable();
            $table->text('Thanks')->nullable();
            $table->text('Post')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('events', function(Blueprint $table) {
            $table->dropColumn('VenueName');
            $table->dropColumn('Thanks');
            $table->dropColumn('Post');
        });
    }
}
