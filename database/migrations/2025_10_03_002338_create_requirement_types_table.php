<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Self-referencing foreign key for parent folder
            $table->foreignId('parent_id')->nullable()->constrained('requirement_types')->onDelete('cascade');
            $table->boolean('is_folder')->default(false); // True for TOS, Rubrics, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_types');
    }
};