@extends('layouts.app')

@section('title')
  Daftar Surat Jalan
@endsection

@section('breadcrumb')
   @parent
   <li>Surat Jalan</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
    


      <div class="box-header">
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Surat Jalan Baru</a>
        @if(!empty(session('idpembelian')))
        <a href="{{ route('kirim_barang_toko_detail.index') }}" class="btn btn-info"><i class="fa fa-plus-circle"></i> Surat Jalan Aktif</a>
        @endif
        @if($data !== 0)
          <a onclick="formHold()" class="btn btn-warning"><i class="fa fa-floppy-o"></i> Surat Jalan Hold</a>
        @endif
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>No Po</th>
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

@include('kirim_barang_toko.detail')
@include('kirim_barang_toko.unit')
@include('kirim_barang_toko.list')

@if(session()->has('cetak'))
<script type="text/javascript">
  tampilPDF();
  function tampilPDF(){
    window.open("{{ route('kirim_barang_toko.cetak',Session::get('cetak')) }}");
  }              
</script>
@endif

@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1,list;
$(function(){
   table = $('.tabel-pembelian').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('kirim_barang_toko.data') }}",
       "type" : "GET"
     }
   }); 
   
   table1 = $('.tabel-detail').DataTable({
    });

   $('.tabel-supplier').DataTable();
});

function addForm(){
   $('#modal-supplier').modal('show');        
}


function formHold(){
   $('#list-hold').modal('show');        
}

function showDetail(id){
    $('#modal-detail').modal('show');

    table1.ajax.url("kirim_barang_toko/"+id+"/lihat");
    table1.ajax.reload();
}

function deleteData(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "kirim_barang_toko/"+id,
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

$(function(){
list = $('.list-hold').DataTable( {
  "scrollX": true
  });
});


$('.modal').on('shown.bs.modal', function() {
  list.columns.adjust();
})

</script>
@endsection
