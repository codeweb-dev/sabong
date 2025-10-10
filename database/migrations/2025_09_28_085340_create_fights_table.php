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
        Schema::create('fights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->integer('fight_number');
            $table->decimal('meron_bet', 12, 2)->default(0);
            $table->decimal('wala_bet', 12, 2)->default(0);
            $table->boolean('meron')->default(true);
            $table->boolean('wala')->default(true);
            $table->string('fighter_a')->nullable();
            $table->string('fighter_b')->nullable();
            $table->enum('status', ['pending', 'start', 'open', 'close', 'done'])->default('pending');
            $table->string('winner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fights');
    }
};
