@extends('layouts.app')

@section('title')
  Daftar Harga Produk
@endsection

@section('breadcrumb')
   @parent
   <li>Produk</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
    
      <div class="box-header">
      </div>
    
<div class="box-body">  

<table class="table table-striped tabel-produk">
<thead>
   <tr>
      <th>Kode Produk</th>
      <th>Nama Produk</th>
      <th>Stok</th>
      @foreach($tenor as $list)
      <th>Tenor {{$list->pekan}} Minggu</th>
      @endforeach
      <th>Harga Normal</th>
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
var table;
$(function(){
   table = $('.tabel-produk').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('cek_harga.data') }}",
       "type" : "GET"
     }
   }); 
});
</script>
@endsection