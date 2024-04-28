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
        Schema::create('products', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->text('content');
			$table->integer('sort_number')->default(0);
			$table->unsignedBigInteger('user_id')->nullable();
			$table->unsignedBigInteger('category_id')->nullable();
			$table->unsignedTinyInteger('is_active')->default(0)->index();
			$table->timestamps();
			$table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
