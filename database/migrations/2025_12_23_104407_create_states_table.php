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
        Schema::create('states', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->unsignedBigInteger('country_id');
            $table->string('country_code');
            $table->string('fips_code')->nullable();
            $table->string('iso2');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();

            $table->tinyInteger('flag')->default(0);
            $table->text('wikiDataId')->nullable();


            // optional FK
             $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
