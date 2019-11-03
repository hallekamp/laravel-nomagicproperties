<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestModelTable extends Migration
{
    public function up()
    {
        Schema::create('testmodels', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('string');
            $table->text('text');
            $table->text('casted_data');
            $table->integer('integer')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
