<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('state_id');
            $table->string('state_code');
            $table->unsignedBigInteger('country_id');
            $table->string('country_code');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
            $table->tinyInteger('flag')->default(0);
            $table->string('wikiDataId')->nullable();


            // optional FK
             $table->foreign('state_id')->references('id')->on('states')->cascadeOnDelete();
             $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
