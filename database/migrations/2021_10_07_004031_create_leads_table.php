<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->longText('lead_id');
            $table->longText('nombre')->nullable();
            $table->longText('telefono')->nullable();
            $table->longText('email')->nullable();
            $table->longText('origen')->nullable();
            $table->longText('responsable')->nullable();
            $table->longText('motivo_compra')->nullable();
            $table->longText('canal_ventas')->nullable();
            $table->longText('desarrollo')->nullable();
            $table->longText('motivo_descalificacion')->nullable();
            $table->longText('estatus')->nullable();
            $table->longText('bitrix_creado_por')->nullable();
            $table->longText('bitrix_creado_el')->nullable();
            $table->longText('bitrix_modificado_el')->nullable();
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
        Schema::dropIfExists('leads');
    }
}
