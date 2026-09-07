<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('disease_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_path');
            $table->integer('ai_class_id')->nullable();
            $table->string('ai_class_name')->nullable();
            $table->decimal('confidence', 8, 6)->nullable();
            $table->decimal('confidence_percent', 6, 2)->nullable();
            $table->boolean('needs_retake')->default(false);
            $table->text('message')->nullable();
            $table->json('top_predictions')->nullable();
            $table->json('raw_ai_response')->nullable();
            $table->string('status')->default('success');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};
