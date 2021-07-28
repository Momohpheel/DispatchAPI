<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('trans_description')->nullable();
            $table->string('datetime')->nullable();
            $table->string('trans_status')->nullable();
            $table->foreignId('order_id')->constrained()->nullable();
            $table->string('reference_num')->nullable();
            $table->enum('status', ['success', 'failed'])->nullable();
            $table->string('amount')->nullable();
            $table->string('origin_of_payment')->nullable();
            $table->string('paystack_message')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
