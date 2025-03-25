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
        Schema::create('float_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('bank_id');
            $table->string('name')->unique();
            $table->string('account_number')->nullable();
            $table->double('balance')->default(0);
            $table->unsignedBigInteger('insert_by')->nullable();
            $table->unsignedBigInteger('update_by')->nullable();
            $table->string('currency')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('insert_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('update_by')->references('id')->on('users')->onDelete('set null');
        
            $table->unique(['bank_id', 'account_number']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('float_accounts');
    }
};
