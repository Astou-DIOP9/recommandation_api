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
        if (! Schema::hasTable('product_views')) {
            Schema::create('product_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id', 100)->nullable();
                $table->timestamp('viewed_at')->useCurrent();
                $table->index(['product_id', 'viewed_at']);
                $table->index('product_id');
                $table->index('user_id');
                $table->index('session_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
