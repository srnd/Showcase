<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Init extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $types = ['app', 'game', 'site', 'hardware', 'other'];

        \Schema::create('batches', function(Blueprint $table) {
            $table->string('Id')->primary();
            $table->string('Webname');
            $table->string('Name');
            $table->timestamp('StartsAt');
            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('events', function(Blueprint $table) {
            $table->string('Id')->primary();
            $table->string('Region');
            $table->string('Webname');
            $table->string('Name');
            $table->string('BatchId');

            $table->string('Timezone');

            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('registrations', function(Blueprint $table) {
            $table->string('Id')->primary();
            $table->string('EventId');

            $table->string('FirstName');
            $table->string('LastName');
            $table->string('Email')->nullable();

            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('ideas', function(Blueprint $table) use ($types) {
            $table->string('Id')->primary();
            $table->string('EventId');

            $table->string('Idea');
            $table->enum('Type', $types);

            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('teams', function(Blueprint $table) use ($types) {
            $table->string('Id')->primary();
            $table->string('EventId');

            $table->integer('Sort');
            $table->string('Name');
            $table->string('Description')->nullable();
            $table->enum('Type', $types);
            $table->string('Link')->nullable();

            $table->boolean('IsPresenting')->default(false);

            $table->string('PhotoUrl')->nullable();
            $table->string('PhotoUrlLarge')->nullable();
            $table->string('PhotoUrlMedium')->nullable();
            $table->string('PhotoUrlSmall')->nullable();

            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('teams_members', function(Blueprint $table) {
            $table->increments('Id');
            $table->string('TeamId');
            $table->string('RegistrationId');
            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });

        \Schema::create('photos', function(Blueprint $table) {
            $table->string('Id')->primary();
            $table->string('EventId');

            $table->string('Url');
            $table->string('UrlLarge');
            $table->string('UrlMedium');
            $table->string('UrlSmall');

            $table->timestamp('CreatedAt');
            $table->timestamp('UpdatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('photos');
        \Schema::drop('teams_members');
        \Schema::drop('teams');
        \Schema::drop('ideas');
        \Schema::drop('registrations');
        \Schema::drop('events');
        \Schema::drop('batches');
    }
}
