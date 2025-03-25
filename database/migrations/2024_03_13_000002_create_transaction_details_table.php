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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('float_account_id');
            $table->string('type')->default('transaction');//counterpart
            $table->double('amount');
            $table->string('reference')->unique()->nullable();
            $table->json('params')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('float_account_id')->references('id')->on('float_accounts')->onDelete('cascade');
            
            $table->unique(['transaction_id', 'float_account_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
