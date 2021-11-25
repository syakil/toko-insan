@extends('layouts.app')

@section('title')
  Stok Write Off  
@endsection

@section('breadcrumb')
   @parent
   <li>stok_wo</li>
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
                <th>Tanggal Transaksi</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Stok</th>
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
      "url" : "{{ route('stok_wo.data') }}",
      "type" : "GET"
    },
  }); 
});
</script>

@endsection