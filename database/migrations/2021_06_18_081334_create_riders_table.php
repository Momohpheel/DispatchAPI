<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('workname')->unique();
            $table->string('phone')->nullable();
            $table->foreignId('partner_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->string('password');
            //$table->string('code_name');
            $table->string('earnings')->nullable();
            $table->decimal('latitude', 11,7)->nullable();
            $table->decimal('longitude', 11,7)->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_available')->default(true);
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
        Schema::dropIfExists('riders');
    }
}
