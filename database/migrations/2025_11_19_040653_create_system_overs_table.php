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
        Schema::create('system_overs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fight_id')->constrained('fights')->onDelete('cascade');
            $table->enum('side', ['meron', 'wala']);
            $table->decimal('overflow', 10, 5)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_overs');
    }
};
