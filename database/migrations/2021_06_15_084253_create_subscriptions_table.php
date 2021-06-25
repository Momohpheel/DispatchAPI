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
            $table->string('name', ['Free', 'Starter', 'Business', 'Enterprise']);
            $table->string('price');
            $table->timestamps();
        });

        DB::table('subscriptions')->insert(
            array(
                'id' => 1,
                'name' => 'Free',
                'price' => '0'
            ),
            array(
                'id' => 2,
                'name' => 'Starter',
                'price' => '17000'
            ),
            array(
                'id' => 3,
                'name' => 'Business',
                'price' => '30000'
            ),
            array(
                'id' => 4,
                'name' => 'Enterprise',
                'price' => '75000'
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
