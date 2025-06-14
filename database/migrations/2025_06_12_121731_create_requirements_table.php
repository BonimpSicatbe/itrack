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
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->date('due'); // date when the requirement is due
            $table->string('target'); // college or department
            $table->integer('target_id'); // id of the college or department
            $table->string('status')->default('pending'); // pending, completed
            $table->string('priority')->default('normal'); // low, normal, high
            $table->string('created_by'); // user id or name who created the requirement
            $table->string('updated_by')->nullable(); // user id or name who updated the requirement
            $table->string('archived_by')->nullable(); // user id or name who archived the requirement
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
