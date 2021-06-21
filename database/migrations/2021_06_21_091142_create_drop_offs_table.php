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
            $table->decimal('d_latitude', 11,7);
            $table->decimal('d_longitude', 11,7);
            $table->string('product_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_email')->nullable();
            $table->string('quantity')->nullable();
            $table->foreignId('rider_id')->constrained();
            $table->timestamp('started_time')->nullable();
            $table->timestamp('ended_time')->nullable();
            $table->string('price');
            $table->string('status', ['done', 'pending']);
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
