<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Rename 'nom' → 'title' for frontend compatibility
            if (Schema::hasColumn('products', 'nom') && ! Schema::hasColumn('products', 'title')) {
                $table->renameColumn('nom', 'title');
            }

            if (! Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('description');
            }

            if (! Schema::hasColumn('products', 'discount_price')) {
                $table->unsignedBigInteger('discount_price')->nullable()->after('price');
            }

            if (! Schema::hasColumn('products', 'in_stock')) {
                $table->boolean('in_stock')->default(true)->after('discount_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'title') && ! Schema::hasColumn('products', 'nom')) {
                $table->renameColumn('title', 'nom');
            }
            if (Schema::hasColumn('products', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('products', 'discount_price')) {
                $table->dropColumn('discount_price');
            }
            if (Schema::hasColumn('products', 'in_stock')) {
                $table->dropColumn('in_stock');
            }
        });
    }
};
