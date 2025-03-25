<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('reference');
        });
    }

    public function down()
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
    }
};