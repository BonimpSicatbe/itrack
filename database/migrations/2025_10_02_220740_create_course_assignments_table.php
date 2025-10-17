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
        Schema::create('course_assignments', function (Blueprint $table) {
            $table->id('assignment_id'); // Custom primary key name, as per your SQL

            // Foreign Key for the Course
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('restrict') // Prevents deleting the course if assignments exist (for retention)
                  ->onUpdate('cascade');

            // Foreign Key for the Professor (referencing the 'users' table 'id' column)
            $table->foreignId('professor_id')
                  ->constrained('users') // Assumes your users table is named 'users'
                  ->onDelete('restrict') // Crucial for retaining historical data on inactive professors
                  ->onUpdate('cascade');
                    
            // 🔥 MODIFIED: Foreign Key for the Semester ID
            $table->foreignId('semester_id')
                  ->constrained('semesters') // Assumes your semesters table is named 'semesters'
                  ->onDelete('restrict') // Crucial for retaining historical data
                  ->onUpdate('cascade');

            $table->date('assignment_date'); // Date professor was assigned

            $table->timestamps();

            // 🔥 MODIFIED UNIQUE constraint: 
            // Now prevents the same professor from being assigned to the same course in the same semester multiple times
            // But allows multiple different professors to be assigned to the same course in the same semester
            $table->unique(['course_id', 'professor_id', 'semester_id'], 'unique_professor_assignment'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_assignments');
    }
};