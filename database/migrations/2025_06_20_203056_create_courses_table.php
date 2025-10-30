<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_courses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code', 50);
            $table->string('course_name');
            $table->text('description')->nullable();
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('course_type_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};