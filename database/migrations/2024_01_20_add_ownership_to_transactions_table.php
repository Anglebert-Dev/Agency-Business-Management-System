<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('insert_by')->nullable();
            $table->unsignedBigInteger('update_by')->nullable();
            
            $table->foreign('insert_by')->references('id')->on('users');
            $table->foreign('update_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['insert_by']);
            $table->dropForeign(['update_by']);
            $table->dropColumn(['insert_by', 'update_by']);
        });
    }
};