<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('plate_number');
            $table->string('earning')->nullable();
            $table->enum('type', ['bike', 'van','car']);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_removed')->default(false);
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
        Schema::dropIfExists('vehicles');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');


    }
}
