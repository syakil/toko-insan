@extends('layouts.app')

@section('title')
  Terima Produk Write OFF
@endsection

@section('breadcrumb')
   @parent
   <li>pembelian</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
  
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

    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Tanggal Input</th>
      <th>Kode Produk </th>
      <th>Nama Produk</th>
      <th>Qty</th>
      <th>Total Harga Jual</th>
      <th>Total Harga Beli</th>
      <th width="100">Aksi</th>
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

var table, save_method, table1;
$(function(){
   table = $('.tabel-pembelian').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('write_off.listAdmin') }}",
       "type" : "GET"
     }
   }); 
});

function deleteData(id){
  var url = "{{route('write_off.proses',':id')}}"
  url = url.replace(':id',id);
  swal({
      title: "Anda Yakin Ingin Memprosessnya?",
      text: "Data Yang Suda Di Proses, Tidak Bisa DiKembalikan!",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    })
  .then((willDelete) => {
      if (willDelete) {
        $.ajax({
          url : url,
          type : "GET",
          data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
          success : function(data){
            table.ajax.reload(); 
            swal("Produk " + id + " Berhasil Di Proses!", {icon: "success",});
          },
          error : function(){
            swal("Produk " + id + " Gagal Di Proses!", {icon: "error",});
          }
        });

      }else {
        swal("Data Anda Masih Aman!",{ 
        icon: "info",
      })
    }
  });
}

</script>
@endsection
