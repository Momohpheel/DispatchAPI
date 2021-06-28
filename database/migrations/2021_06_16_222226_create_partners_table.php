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
            $table->foreignId('subscription_id')->constrained('subscriptions')->default(1);
            $table->enum('subscription_status', ['paid', 'not paid'])->nullable();
            $table->date('subscription_date')->nullable();
            $table->date('subscription_expiry_date')->nullable();
            $table->integer('order_count_per_day')->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_paused')->default(false);
            $table->boolean('is_top_partner')->default(false);
            $table->date('top_partner_pay_date')->nullable();
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
