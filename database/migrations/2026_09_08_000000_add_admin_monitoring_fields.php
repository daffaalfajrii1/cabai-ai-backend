<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        Schema::table('detections', function (Blueprint $table) {
            if (! Schema::hasColumn('detections', 'valid_input')) {
                $table->boolean('valid_input')->default(true)->after('needs_retake');
            }

            if (! Schema::hasColumn('detections', 'classification_source')) {
                $table->string('classification_source')->nullable()->after('valid_input');
            }

            if (! Schema::hasColumn('detections', 'review_status')) {
                $table->string('review_status')->nullable()->default('pending')->after('status');
            }

            if (! Schema::hasColumn('detections', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('detections', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('detections', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('detections', 'corrected_disease_id')) {
                $table->foreignId('corrected_disease_id')->nullable()->after('review_notes')->constrained('diseases')->nullOnDelete();
            }
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');

        Schema::table('detections', function (Blueprint $table) {
            if (Schema::hasColumn('detections', 'corrected_disease_id')) {
                $table->dropConstrainedForeignId('corrected_disease_id');
            }

            if (Schema::hasColumn('detections', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }

            foreach (['valid_input', 'classification_source', 'review_status', 'reviewed_at', 'review_notes'] as $column) {
                if (Schema::hasColumn('detections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
