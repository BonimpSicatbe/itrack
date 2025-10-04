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

            // UNIQUE constraint: Ensures a course is taught by only one professor in a specific semester/year
            // 🔥 MODIFIED: The unique constraint is updated to use semester_id instead of year and semester string
            $table->unique(['course_id', 'semester_id'], 'unique_term_assignment'); 
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