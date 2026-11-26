<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('main_image')->nullable();
            $table->string('city');
            $table->string('region');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('best_period')->nullable();
            $table->text('tourist_info')->nullable();
            $table->text('facilities')->nullable(); // JSON string
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('views')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('destination_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('destination_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tourism_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['destination_id', 'tourism_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_category');
        Schema::dropIfExists('destination_images');
        Schema::dropIfExists('destinations');
    }
};
