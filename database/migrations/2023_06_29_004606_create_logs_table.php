<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->tinyInteger('type')->nullable(false);
            $table->string('error_type')->nullable(false);
            $table->string('filename')->nullable(false);
            $table->string('merchant_code')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('transaction_no')->nullable();
            $table->string('terminal_no')->nullable();
            $table->string('error_description')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
