<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BuatTabelMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member', function (Blueprint $table) {
            $table->increments('id_member');
            $table->bigInteger('kode_member')->unsigned();
            $table->string('nama', 100);
            $table->text('alamat');
            $table->string('telpon', 20);
            $table->string('unit', 20);
            $table->string('jenis', 30);
            $table->bigInteger('plafond');
            $table->string('status', 2);
            
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
        //
    }
}
