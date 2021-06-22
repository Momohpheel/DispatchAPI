<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('code_name')->unique();
            $table->foreignId('subscription_id')->constrained()->default(1);
            $table->enum('subscription_status', ['paid', 'not paid']);
            $table->integer('order_count_per_day');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_paused')->default(false);
            $table->boolean('is_top_partner')->default(false);
            $table->enum('top_partner_charge_status', ['paid', 'not paid'])->nullable();
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
        Schema::dropIfExists('partners');
    }
}
