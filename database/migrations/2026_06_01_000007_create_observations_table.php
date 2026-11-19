<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_id')->constrained()->cascadeOnDelete();
            $table->date('observed_on');
            $table->string('category', 40);
            $table->string('severity', 20)->nullable();
            $table->string('growth_stage', 40)->nullable();
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
