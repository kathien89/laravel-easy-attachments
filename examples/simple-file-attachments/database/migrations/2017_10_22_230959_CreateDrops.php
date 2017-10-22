<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('memo');
            $table->timestamps();
        });
        Schema::create('drop_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('drop_id');
            $table->integer('attachment_id');
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
        Schema::dropIfExists('drops');
        Schema::dropIfExists('drop_attachments');
    }
}
