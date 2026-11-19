<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_records', function (Blueprint $table) {
            $table->id();
            $table->string('worker_name');
            $table->string('task');
            $table->date('work_date');
            $table->decimal('days_worked', 6, 2)->default(1);
            $table->decimal('rate_per_day', 12, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);
            $table->foreignId('planting_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_records');
    }
};
