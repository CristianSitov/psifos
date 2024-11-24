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
        Schema::table('voting_hours', function (Blueprint $table) {
            $table->integer('year')->after('id')->default(2024);
        });
        Schema::table('voting_results', function (Blueprint $table) {
            $table->integer('year')->after('id')->default(2024);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voting_results', function (Blueprint $table) {
            $table->dropColumn('year');
        });
        Schema::table('voting_hours', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
