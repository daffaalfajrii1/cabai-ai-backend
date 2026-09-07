<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->string('ai_class_name')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('scientific_name')->nullable();
            $table->text('description')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('cause')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prevention')->nullable();
            $table->boolean('is_healthy')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diseases');
    }
};
