<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrossValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cross_validations', function (Blueprint $table) {
            $table->integer('id')->nullable(false);
            $table->string('cccode')->nullable(false);
            $table->string('trn_date')->nullable(false);
            $table->string('ter_no')->nullable(false);
            $table->string('filename')->nullable(false);
            $table->string('gross')->nullable()->default('0.00');
            $table->string('card')->nullable()->default('0.00');
            $table->string('epay')->nullable()->default('0.00');
            $table->string('dcard')->nullable()->default('0.00');
            $table->string('gc_sls')->nullable()->default('0.00');
            $table->string('other_sls')->nullable()->default('0.00');
            $table->string('check_sls')->nullable()->default('0.00');
            $table->string('grab_sls')->nullable()->default('0.00');
            $table->string('foodpanda_sls')->nullable()->default('0.00');
            $table->string('notrn')->nullable()->default('0.00');
            $table->string('sales_total')->nullable()->default('0.00');
            $table->string('payment')->nullable()->default('0.00');
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
        Schema::dropIfExists('cross_validations');
    }
}
