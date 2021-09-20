<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteCostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_costings', function (Blueprint $table) {
            $table->id();
            $table->string('fuel_cost')->nullable();
            $table->string('bike_fund')->nullable();
            $table->string('ops_fee')->nullable();
            $table->string('easy_log')->nullable();
            $table->string('easy_disp')->nullable();
            $table->string('express')->nullable();
            $table->string('max_km')->nullable();
            $table->string('min_km')->nullable();
            $table->foreignId('partner_id')->constrained();
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
        Schema::dropIfExists('route_costings');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');


    }
}
