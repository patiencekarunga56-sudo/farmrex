<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 40);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->date('purchase_date');
            $table->string('supplier')->nullable();
            $table->foreignId('planting_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_inputs');
    }
};
