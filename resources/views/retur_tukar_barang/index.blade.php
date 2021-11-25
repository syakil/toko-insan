@extends('layouts.app')

@section('title')
  Daftar Surat Jalan
@endsection

@section('breadcrumb')
   @parent
   <li>Surat Jalan</li>
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
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Surat Jalan Baru</a>
        @if(!empty(session('idpembelian')))
        <a href="{{ route('retur_tukar_barang_detail.index') }}" class="btn btn-info"><i class="fa fa-plus-circle"></i> Surat Jalan Aktif</a>
        @endif
        @if($data !== 0)
          <a onclick="formHold()" class="btn btn-warning"><i class="fa fa-floppy-o"></i> Surat Jalan Hold</a>
        @endif
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th>No Surat Jalan</th>
      <th>Supplier</th>
      <th>Tanggal</th>
      <th>Total Item</th>
      <th>Total Harga</th>
      <th width="150">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('retur_tukar_barang.detail')
@include('retur_tukar_barang.supplier')
@include('retur_tukar_barang.list')

@if(session()->has('cetak'))
<script type="text/javascript">
  tampilPDF();
  function tampilPDF(){
    window.open("{{ route('retur_tukar_barang.cetak',Session::get('cetak')) }}");
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
      "url" : "{{ route('retur_tukar_barang.data') }}",
      "type" : "GET"
    }
  }); 
   
  table1 = $('.tabel-detail').DataTable({});  
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
  url = "{{route('retur_tukar_barang.detail',':id')}}";
  url = url.replace(':id',id);
  table1.ajax.url(url);
  table1.ajax.reload();
}

function deleteData(id){

  swal({
    title: "Anda Akan Menghapus Surat Jalan Ini?",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete) {
      url = "{{route('retur_tukar_barang.delete',':id')}}";
      url = url.replace(':id',id);
      $.ajax({
        url : url,
        type : "GET",
        data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
        success : function(data){
          
          $('#list-hold').modal('hide');        
          swal("Selamat !", 'Data Berhasil Di Hapus!', "success").then(function(){
            // window.setTimeout(function(){location.reload()},5000)
            location.reload();
          });  

        },
        error : function(){
          swal("Maaf !", 'Tidak Dapat Menghapus Data!', "error"); 
        }
      });

    } else {
      swal("Data Anda Aman !");
    }
  });

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
