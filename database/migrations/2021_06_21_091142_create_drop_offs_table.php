<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drop_offs', function (Blueprint $table) {
            $table->id();
            $table->text('d_address')->nullable();
            $table->decimal('d_latitude', 11,7)->nullable();
            $table->decimal('d_longitude', 11,7)->nullable();
            $table->string('product_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_email')->nullable();
            $table->string('quantity')->nullable();
            $table->foreignId('rider_id')->constrained();
            $table->foreignId('partner_id')->constrained();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('price')->nullable();
            $table->enum('payment_status', ['paid', 'not paid'])->nullable();
            $table->enum('status', ['not taken', 'failed', 'ongoing', 'completed']);
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
        Schema::dropIfExists('drop_offs');
    }
}
