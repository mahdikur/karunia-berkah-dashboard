<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('expenses', 'purchase_order_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('purchase_order_id')->nullable()->after('created_by')
                    ->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
        });
    }
};
