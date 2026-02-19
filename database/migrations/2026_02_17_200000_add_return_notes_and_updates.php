<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Return Notes (Retur)
        Schema::create('return_notes', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('delivery_note_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->date('return_date');
            $table->enum('status', ['draft', 'confirmed', 'processed'])->default('draft');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['client_id', 'status']);
        });

        // Return Note Items
        Schema::create('return_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_note_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_returned', 10, 2);
            $table->string('unit');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // Track edit history for PO (to sync with invoices/DN)
        if (!Schema::hasColumn('purchase_orders', 'last_edited_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->timestamp('last_edited_at')->nullable()->after('cancelled_reason');
            });
        }

        // Add unavailable flag to delivery note items
        if (!Schema::hasColumn('delivery_note_items', 'is_unavailable')) {
            Schema::table('delivery_note_items', function (Blueprint $table) {
                $table->boolean('is_unavailable')->default(false)->after('unit');
                $table->text('unavailable_reason')->nullable()->after('is_unavailable');
            });
        }
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_note_items', 'is_unavailable')) {
                $table->dropColumn(['is_unavailable', 'unavailable_reason']);
            }
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'last_edited_at')) {
                $table->dropColumn('last_edited_at');
            }
        });
        Schema::dropIfExists('return_note_items');
        Schema::dropIfExists('return_notes');
    }
};
