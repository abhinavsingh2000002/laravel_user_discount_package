<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('discount_id');
            $table->integer('usage_count')->default(0);
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'discount_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_discounts');
    }
};
