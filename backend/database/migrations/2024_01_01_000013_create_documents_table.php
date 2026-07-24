<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('cases')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('document_folders')->nullOnDelete();

            $table->enum('category', ['contract', 'agreement', 'evidence', 'court_file', 'client_document', 'other'])
                ->default('other');

            $table->string('name'); // display name, editable independent of the stored filename
            $table->string('original_filename');
            $table->string('disk')->default('local');
            $table->string('path'); // storage path on disk — never exposed to the client directly
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');

            // Versioning: a new upload of the "same" document creates a new row pointing
            // back at the first version via root_document_id, with an incrementing `version`.
            $table->foreignId('root_document_id')->nullable()->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_latest_version')->default(true);

            $table->boolean('client_visible')->default(false);
            $table->unsignedInteger('download_count')->default(0);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['firm_id', 'case_id']);
            $table->index(['firm_id', 'client_id']);
            $table->index(['root_document_id', 'is_latest_version']);
            $table->fullText(['name', 'original_filename']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
