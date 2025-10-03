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
                  
            // Historical Tracking Fields
            $table->integer('year');
            $table->string('semester', 50); // e.g., 'Fall', 'Spring'
            $table->date('assignment_date'); // Date professor was assigned

            $table->timestamps();

            // UNIQUE constraint: Ensures a course is taught by only one professor in a specific semester/year
            $table->unique(['course_id', 'year', 'semester'], 'unique_term_assignment'); 
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