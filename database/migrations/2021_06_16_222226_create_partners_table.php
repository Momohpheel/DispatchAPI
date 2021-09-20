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

            $table->string('image')->nullable();
            $table->string('description')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();


            $table->string('password');
            $table->string('code_name')->unique();
            $table->string('wallet')->default(0);
            $table->foreignId('subscription_id')->constrained('subscriptions')->nullable();
            $table->enum('subscription_status', ['paid', 'not paid'])->nullable();
            $table->date('subscription_date')->nullable();
            $table->date('subscription_expiry_date')->nullable();
            $table->integer('order_count_per_day')->nullable();
            $table->integer('vehicle_count')->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_paused')->default(false);
            $table->boolean('is_top_partner')->default(false);
            $table->date('top_partner_expiry_date')->nullable();
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
        Schema::dropIfExists('partners');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    }
}
