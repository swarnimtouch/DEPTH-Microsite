<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('iso3');
            $table->string('iso2');
            $table->string('phonecode');
            $table->string('capital');
            $table->string('currency');
            $table->string('currency_symbol');
            $table->string('tld');
            $table->string('native')->nullable();
            $table->string('region');
            $table->string('subregion');
            $table->text('timezones');
            $table->text('translations')->nullable();
            $table->text('latitude');
            $table->text('longitude');
            $table->text('emoji');
            $table->text('emojiU');
            $table->tinyInteger('flag')->default(0);
            $table->text('wikiDataId')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};

