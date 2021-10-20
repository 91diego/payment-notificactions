<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_sells', function (Blueprint $table) {
            $table->id();
            $table->longText('negociacion_bitrix_id')->nullable();
            $table->longText('prospecto_bitrix_id')->nullable();
            $table->longText('negociacion_venta_bitrix_id')->nullable();
            $table->longText('etapa')->nullable();
            $table->longText('tipo')->nullable();
            $table->longText('gerente')->nullable();
            $table->longText('responsable')->nullable();
            $table->longText('origen')->nullable();
            $table->longText('motivo_compra')->nullable();
            $table->longText('canal_venta')->nullable();
            $table->longText('producto')->nullable();
            $table->longText('precio')->nullable();
            $table->longText('motivo_descalificacion')->nullable();
            $table->longText('motivo_cancelacion_apartado')->nullable();
            $table->longText('desarrollo')->nullable();
            $table->longText('desarrollo_interes')->nullable();
            $table->longText('tipo_visita')->nullable();
            $table->longText('negociacion_descalificado_comentarios')->nullable();
            $table->longText('hora_exacta_visita')->nullable();
            $table->longText('apartado_el')->nullable();
            $table->longText('vendido_el')->nullable();
            $table->longText('compromiso_entrega_el')->nullable();
            $table->longText('compromiso_entrega_reproyectado_el')->nullable();
            $table->longText('bitrix_created_el')->nullable();
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
        Schema::dropIfExists('deal_sells');
    }
}
