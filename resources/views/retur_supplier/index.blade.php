@extends('layouts.app')

@section('title')
  Daftar Retur Barang
@endsection

@section('breadcrumb')
   @parent
   <li>Retur Barang</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
    


      <div class="box-header">
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Surat Keluar Baru</a>
        @if(!empty(session('idpembelian')))
        <a href="{{ route('retur_supplier_detail.index') }}" class="btn btn-info"><i class="fa fa-plus-circle"></i> Surat Keluar Aktif</a>
        @endif
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Tanggal</th>
      <th>Toko</th>
      <th>Total Item</th>
      <th>Total Harga</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('retur_supplier.detail')
@include('retur_supplier.supplier')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
   table = $('.tabel-pembelian').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('retur_supplier.data') }}",
       "type" : "GET"
     }
   }); 
   
   table1 = $('.tabel-detail').DataTable({
     "dom" : 'Brt',
     "bSort" : false,
     "processing" : true
    });

   $('.tabel-supplier').DataTable();
});

function addForm(){
   $('#modal-supplier').modal('show');        
}

function showDetail(id){
    $('#modal-detail').modal('show');

    table1.ajax.url("retur_supplier/"+id+"/lihat");
    table1.ajax.reload();
}

function deleteData(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "retur_supplier/"+id,
       type : "POST",
       data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
       success : function(data){
         table.ajax.reload();
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
       }
     });
   }
}
</script>
@endsection