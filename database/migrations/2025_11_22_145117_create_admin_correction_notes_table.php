<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_correction_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitted_requirement_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->text('correction_notes');
            $table->string('file_name')->nullable()->comment('Original file name when note was created');
            $table->string('status')->default('pending')->comment('pending, addressed, resolved');
            $table->timestamp('addressed_at')->nullable();
            $table->timestamps();

            // Updated composite indexes
            $table->index(['submitted_requirement_id', 'status']);
            $table->index(['requirement_id', 'course_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_correction_notes');
    }
};