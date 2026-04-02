<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('brands', 'erp_brand_id')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('erp_brand_id')->nullable()->after('name');
            $table->unique('erp_brand_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('brands', 'erp_brand_id')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['erp_brand_id']);
            $table->dropColumn('erp_brand_id');
        });
    }
};
