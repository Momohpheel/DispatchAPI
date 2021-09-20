<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('data')->nullable();
            $table->string('type')->nullable();
            $table->foreignId('user_id')->constrained()->nullable();
            $table->foreignId('rider_id')->constrained()->nullable();
            $table->foreignId('partner_id')->constrained()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('transaction_logs');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        //Schema::dropIfExists('transaction_logs');
    }
}
