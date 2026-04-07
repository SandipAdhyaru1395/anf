<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->decimal('maximum_amount', 12, 2)->nullable()->after('minimum_amount');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_methods', function (Blueprint $table) {
            $table->dropColumn('maximum_amount');
        });
    }
};
