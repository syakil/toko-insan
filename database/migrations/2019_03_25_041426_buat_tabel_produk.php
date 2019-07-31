<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BuatTabelProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->increments('id_produk');
            $table->string('unit',10);
            $table->bigInteger('kode_produk')->unsigned();
            $table->integer('id_kategori')->unsigned();
            $table->string('nama_produk', 100);
            $table->string('merk', 50);
            $table->bigInteger('harga_beli')->unsigned();
            $table->integer('diskon')->unsigned();
            $table->bigInteger('harga_jual')->unsigned();
            $table->bigInteger('harga_jual_member_insan')->unsigned();
            $table->bigInteger('harga_jual_insan')->unsigned();
            $table->bigInteger('harga_jual_pabrik')->unsigned();
            $table->integer('stok')->unsigned();
                            
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
        Schema::dropIfExists('produk');
    }
}
