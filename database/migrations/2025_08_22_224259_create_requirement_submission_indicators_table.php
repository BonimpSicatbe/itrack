<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requirement_submission_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            
            // Use a shorter custom name for the unique constraint
            $table->unique(['requirement_id', 'user_id', 'course_id'], 'req_user_course_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requirement_submission_indicators');
    }
};