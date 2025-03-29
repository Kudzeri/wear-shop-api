<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryServicesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_delivery_service');
            $table->string('id_delivery_service_1c');
            $table->string('delivery_service');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_services');
    }
}
