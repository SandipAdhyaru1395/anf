<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'contact_name')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            $table->string('contact_name', 255)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('branches', 'contact_name')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('contact_name');
        });
    }
};
