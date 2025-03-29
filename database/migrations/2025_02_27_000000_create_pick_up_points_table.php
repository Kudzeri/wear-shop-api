<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickUpPointsTable extends Migration
{
    public function up()
    {
        Schema::create('pick_up_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pickup_point');
            $table->string('id_pickup_point_1c');
            $table->string('pick_up_point');
            $table->unsignedBigInteger('id_delivery_service');
            $table->string('id_delivery_service_1c');
            $table->timestamps();

            // Optionally add foreign key constraints if needed:
            // $table->foreign('id_delivery_service')->references('id')->on('delivery_services');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pick_up_points');
    }
}
