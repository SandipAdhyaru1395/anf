<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'pay_later')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('pay_later')->default(false);
            });
        }

        if (Schema::hasColumn('customer_groups', 'pay_later')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                $table->dropColumn('pay_later');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('customer_groups', 'pay_later')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                $table->boolean('pay_later')->default(false);
            });
        }

        if (Schema::hasColumn('customers', 'pay_later')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('pay_later');
            });
        }
    }
};
