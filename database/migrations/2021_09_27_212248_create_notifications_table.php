<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('neodata_id', 30);
            $table->string('type');
            $table->string('customer_name', 100)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('development', 80)->nullable();
            $table->enum('status', ['SENT', 'ERROR'])->default('APLICADO');
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
        Schema::dropIfExists('notifications');
    }
}
