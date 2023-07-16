<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateThresholdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threshold', function (Blueprint $table) {
            $table->id();
            $table->string('value_from');
            $table->string('value_to');
        });

        DB::table('threshold')->insert([
            'value_from'=>'-0.99',
            'value_to'=>'0.99'
         ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threshold');
    }
}
