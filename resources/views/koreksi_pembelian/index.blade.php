@extends('layouts.app')

@section('title')
  Daftar Koreksi Detail Pembelian
@endsection

@section('breadcrumb')
   @parent
   <li>pembelian</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="icon fa fa-ban"></i> Alert!</h4>
      {{$errors->first()}} <br>
    </div>
  @endif

    <div class="box">
      <div class="box-header">

      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>No Po. </th>
      <th>Kode Produk</th>
      <th>Nama Produk </th>
      <th>Total Item</th>
      <th>Total Stok Saat Ini</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('koreksi_pembelian.show')

@endsection

@section('script')
<script type="text/javascript">
var table;
$(function(){
   table = $('.tabel-pembelian').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('koreksi_pembelian.listData') }}",
       "type" : "GET"
     }
   }); 
});
</script>

<script>
function showDetail(id){
$('#showModal').modal('show');

var url ="{{ route('koreksi_pembelian.show',':id')}}";
url = url.replace(':id',id);
$.ajax({
    url : url,
    type: "GET",
    data: {id:id},
    dataType: 'json',
    success: function(data){      
      $('#qty').val(data.qty);      
      $('#id').val(data.id);      
    },
    error : function(){
        alert("Data Tidak Ada");
    }
}); 


}
</script>
  
<script>
function deleteId(id){
var url ="{{ route('koreksi_pembelian.delete',':id')}}";
url = url.replace(':id',id);
$.ajax({
    url : url,
    type: "GET",
    data: {id:id},
    dataType: 'json',
    success: function(data){
      table.ajax.reload();   
      alert("Koreksi Di Tolak");
    },
    error : function(){
        alert("Data Tidak Ada");
    }
}); 



}

</script>
@endsection
