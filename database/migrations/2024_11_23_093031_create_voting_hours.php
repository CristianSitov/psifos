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
        Schema::create('voting_hours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->string('timestamp')->nullable();
            $table->tinyInteger('is_done')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_hours');
    }
};
