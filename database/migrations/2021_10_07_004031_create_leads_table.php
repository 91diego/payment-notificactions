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
            $table->longText('bitrix_id');
            $table->longText('name')->nullable();
            $table->longText('phone')->nullable();
            $table->longText('email')->nullable();
            $table->longText('origin')->nullable();
            $table->longText('responsable')->nullable();
            $table->longText('purchase_reason')->nullable();
            $table->longText('sales_channel')->nullable();
            $table->longText('development')->nullable();
            $table->longText('disqualification_reason')->nullable();
            $table->longText('status')->nullable();
            $table->longText('bitrix_created_by')->nullable();
            $table->longText('bitrix_created_at')->nullable();
            $table->longText('bitrix_modified_at')->nullable();
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
