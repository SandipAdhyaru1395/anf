<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * null = pending, 0 = rejected, 1 = approved
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_approved')->nullable();
        });

        // Existing approved customers
        DB::table('customers')->whereNotNull('approved_at')->update(['is_approved' => 1]);

        // Active customers without approved_at (legacy) — treat as approved
        DB::table('customers')
            ->whereNull('is_approved')
            ->where('is_active', 1)
            ->update(['is_approved' => 1]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
