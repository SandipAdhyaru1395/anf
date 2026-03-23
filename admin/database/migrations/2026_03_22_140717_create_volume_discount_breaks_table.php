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
        Schema::create('volume_discount_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volume_discount_group_id')->constrained('volume_discount_groups')->cascadeOnDelete();
            $table->integer('from_quantity');
            $table->decimal('discount_percentage', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volume_discount_breaks');
    }
};
