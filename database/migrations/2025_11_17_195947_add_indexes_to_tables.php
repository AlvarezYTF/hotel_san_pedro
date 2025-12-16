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
        // Add indexes to products table
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('status');
            $table->index('quantity');
            $table->index(['status', 'category_id']);
        });

        // Add indexes to sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('product_id');
            $table->index(['sale_id', 'product_id']);
        });

        // Add indexes to repairs table
        Schema::table('repairs', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('repair_status');
            $table->index('repair_date');
            $table->index(['customer_id', 'repair_status']);
        });

        // Add indexes to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['quantity']);
            $table->dropIndex(['status', 'category_id']);
        });

        // Remove indexes from sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['sale_id', 'product_id']);
        });

        // Remove indexes from repairs table
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['repair_status']);
            $table->dropIndex(['repair_date']);
            $table->dropIndex(['customer_id', 'repair_status']);
        });

        // Remove indexes from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
