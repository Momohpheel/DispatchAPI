<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['Free', 'Starter', 'Business', 'Enterprise']);
            $table->string('price');
            $table->string('no_of_orders');
            $table->timestamps();
        });

        DB::table('subscriptions')->insert(
            array(
                'id' => 1,
                'name' => 'Free',
                'price' => 0,
                'no_of_orders' => 5
            ),
        );
        DB::table('subscriptions')->insert(
            array(
                'id' => 2,
                'name' => 'Starter',
                'price' => 17000,
                'no_of_orders' => 15
            ),
        );
        DB::table('subscriptions')->insert(
            array(
                'id' => 3,
                'name' => 'Business',
                'price' => 30000,
                'no_of_orders' => 25
            ),
        );
        DB::table('subscriptions')->insert(
            array(
                'id' => 4,
                'name' => 'Enterprise',
                'price' => 75000,
                'no_of_orders' => 'unlimited'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
