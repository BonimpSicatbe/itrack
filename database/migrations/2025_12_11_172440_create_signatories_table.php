<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('signatories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add columns to submitted_requirements table
        Schema::table('submitted_requirements', function (Blueprint $table) {
            $table->text('signed_document_path')->nullable()->after('admin_notes');
            $table->timestamp('signed_at')->nullable()->after('reviewed_at');
            $table->unsignedBigInteger('signatory_id')->nullable()->after('reviewed_by');
            
            $table->foreign('signatory_id')
                  ->references('id')
                  ->on('signatories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        // Drop foreign key and columns from submitted_requirements
        Schema::table('submitted_requirements', function (Blueprint $table) {
            $table->dropForeign(['signatory_id']);
            $table->dropColumn(['signed_document_path', 'signed_at', 'signatory_id']);
        });

        Schema::dropIfExists('signatories');
    }
};