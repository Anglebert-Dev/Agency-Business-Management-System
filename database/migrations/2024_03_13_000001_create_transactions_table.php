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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('type')->default('deposit');
            $table->string('status');
            $table->double('amount');
            $table->double('fee')->default(0);
            $table->double('amount_after_fee');
            $table->string('reference')->unique()->nullable();
            
            $table->unsignedBigInteger('customer_id')->nullable(); // Make nullable
            $table->string('customer_account_number')->nullable();
            $table->unsignedBigInteger('customer_bank_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Add source and destination account fields
            $table->unsignedBigInteger('source_account_id')->nullable();
            $table->unsignedBigInteger('destination_account_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            // $table->foreign('source_account_id')->references('id')->on('float_accounts');
            // $table->foreign('destination_account_id')->references('id')->on('float_accounts');
            
            $table->unique(['customer_bank_id', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
