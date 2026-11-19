<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('category', 60);
            $table->string('produce')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('buyer')->nullable();
            $table->date('income_date');
            $table->text('description')->nullable();
            $table->foreignId('planting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
