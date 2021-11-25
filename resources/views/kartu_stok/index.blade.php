@extends('layouts.app')

@section('title')
  Kartu Stok
@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
@endsection

@section('content')     



<div class="row">
  <div class="col-xs-12">
    <div class="box">


      <div class="box-body">  
          <table class="table table-striped">
            <thead>
              <tr>
                <th width="20">No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Stok Awal</th>
                <th>Pembelian</th>
                <th>Terima Lebih Kirim</th>
                <th>Terima Retur</th>
                <th>Kirim Barang</th>
                <th>Write OFF</th>
                <th>Stok Akhir</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
  table = $('.table').DataTable({
    "processing" : true,
    "serverside" : true,
    "ajax" : {
      "url" : "{{ route('kartu_stok.data') }}",
      "type" : "GET"
    },
  }); 
});
</script>

@endsection