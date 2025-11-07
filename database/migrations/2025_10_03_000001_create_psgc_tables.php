<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('psgc_regions', function (Blueprint $t) {
        $t->string('code', 9)->primary();   // e.g. 13, 01 …
        $t->string('name');
        $t->timestamps();
    });

    Schema::create('psgc_provinces', function (Blueprint $t) {
        $t->string('code', 12)->primary();  // provCode
        $t->string('name');
        $t->string('region_code', 9)->nullable()->index();
        $t->timestamps();
    });

    Schema::create('psgc_cities', function (Blueprint $t) {
        $t->string('code', 12)->primary();  // citymunCode
        $t->string('name');
        $t->string('province_code', 12)->nullable()->index(); // NCR = null
        $t->string('region_code', 9)->nullable()->index();
        $t->timestamps();
    });

    Schema::create('psgc_barangays', function (Blueprint $t) {
        $t->string('code', 15)->primary();  // brgyCode
        $t->string('name');
        $t->string('city_code', 12)->index();
        $t->timestamps();
    });
}


    public function down(): void
    {
        Schema::dropIfExists('psgc_barangays');
        Schema::dropIfExists('psgc_cities');
        Schema::dropIfExists('psgc_provinces');
        Schema::dropIfExists('psgc_regions');
    }
};
