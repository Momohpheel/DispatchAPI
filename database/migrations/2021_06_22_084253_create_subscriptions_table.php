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
            $table->enum('name', ['Free', 'Economy']);
            $table->string('price');
            $table->timestamps();
        });

        DB::table('subscriptions')->insert(
            array(
                'name' => 'Free',
                'price' => 0
            ),
            array(
                'name' => 'Starter',
                'price' => 17000
            ),
            array(
                'name' => 'Business',
                'price' => 30000
            ),
            array(
                'name' => 'Enterprise',
                'price' => 75000
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
