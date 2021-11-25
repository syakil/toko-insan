@extends('layouts.app')

@section('title')
  Daftar Pembelian
@endsection

@section('breadcrumb')
   @parent
   <li>pembelian</li>
@endsection

@section('content')     
@if ($message = Session::get('error'))
      <script>
        var pesan = "{{$message}}"
        swal("Maaf !", pesan, "error"); 
      </script>
    @elseif ($message = Session::get('success'))
      <script>
        var pesan = "{{$message}}"
        swal("Selamat !", pesan, "success"); 
      </script>
    @endif


<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>No. Pembelian</th>
      <th>Tanggal</th>
      <th>Supplier</th>
      <th>Total Item</th>
      <th>Total Terima</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('terima_po.detail')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
   table = $('.tabel-pembelian').DataTable({
     "processing" : true,
     "serverside" : true,
     "responsive" : true,
     "ajax" : {
       "url" : "{{ route('approve_terima_po.data') }}",
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
   $('#modal-list_terima').modal('show');        
}

function showDetail(id){
    $('#modal-detail').modal('show');

    table1.ajax.url("terima_po/"+id+"/lihat");
    table1.ajax.reload();
}

function deleteData(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "terima_po/"+id,
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