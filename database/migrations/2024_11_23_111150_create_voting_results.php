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
        Schema::create('voting_results', function (Blueprint $table) {
            $table->id();
            $table->string("key");
            $table->integer("county_id")->default(0);
            $table->integer("initial_count_lp")->default(0);
            $table->integer("initial_count_lc")->default(0);
            $table->integer("precincts_count")->default(0);
            $table->integer("LP")->default(0);
            $table->integer("LS")->default(0);
            $table->integer("LSC")->default(0);
            $table->integer("UM")->default(0);
            $table->integer("LT")->default(0);
            $table->integer("presence")->default(0);
            $table->integer("medium_u")->default(0);
            $table->integer("medium_r")->default(0);
            $table->integer("men_18_24")->default(0);
            $table->integer("men_25_34")->default(0);
            $table->integer("men_35_44")->default(0);
            $table->integer("men_45_64")->default(0);
            $table->integer("men_65+")->default(0);
            $table->integer("women_18_24")->default(0);
            $table->integer("women_25_34")->default(0);
            $table->integer("women_35_44")->default(0);
            $table->integer("women_45_64")->default(0);
            $table->integer("women_65+")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_results');
    }
};
