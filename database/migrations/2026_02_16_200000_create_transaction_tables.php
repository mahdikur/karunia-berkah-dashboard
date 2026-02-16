<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('po_date');
            $table->date('delivery_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'cancelled', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'client_id']);
            $table->index(['created_by', 'status']);
        });

        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->decimal('purchase_price', 15, 2)->nullable(); // filled after purchase
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Item Price History
        Schema::create('item_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2);
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->string('reference_type')->nullable(); // po, invoice, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'item_id']);
        });

        // Modals (Capital/Fund tracking)
        Schema::create('modals', function (Blueprint $table) {
            $table->id();
            $table->string('modal_number')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->date('modal_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // Modal Allocations
        Schema::create('modal_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modal_allocations');
        Schema::dropIfExists('modals');
        Schema::dropIfExists('item_price_histories');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
