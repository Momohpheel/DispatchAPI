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
            $table->string('base_fare')->nullable();
            $table->string('cost_perkm')->nullable();
            $table->string('express')->nullable();
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
