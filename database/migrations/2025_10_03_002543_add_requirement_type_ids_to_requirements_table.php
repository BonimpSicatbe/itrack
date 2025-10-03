<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            // Stores selected type IDs as JSON array (e.g., [3, 6, 9])
            $table->json('requirement_type_ids')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropColumn('requirement_type_ids');
        });
    }
};
