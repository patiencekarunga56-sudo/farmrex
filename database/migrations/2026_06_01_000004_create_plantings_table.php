<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->restrictOnDelete();
            $table->foreignId('crop_id')->constrained()->restrictOnDelete();
            $table->date('planting_date');
            $table->date('expected_harvest_date')->nullable();
            $table->decimal('area_acres', 8, 2)->nullable();
            $table->string('growth_stage', 40)->default('Planted');
            $table->string('status', 20)->default('Active');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantings');
    }
};
