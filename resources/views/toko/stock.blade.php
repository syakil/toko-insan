@extends('layouts.app')

@section('title')
  Daftar Produk
@endsection

@section('header')

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
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
            <table class="table table-striped tabel-stock" >
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Stock</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

@include('toko.editstok')

    <!-- /.content -->

@endsection

@section('script')


<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>



<script type="text/javascript">
var table, save_method, table1;
$(function(){
   table = $('.tabel-stock').DataTable({
     "processing" : true,
     "serverside" : true,
     dom: 'Bfrtip',
        buttons: [
            'excel'
        ],
     "ajax" : {
       "url" : "{{ route('stockToko.data') }}",
       "type" : "GET"
     }

   });
   $('div.dataTables_filter input').focus(); 
});
</script>
<script>

function editForm(id){
  $('#modal-form form')[0].reset();   
  url = "{{route('stockToko.edit',':id')}}";
  url = url.replace(':id',id);
  $.ajax({
    url : url,
    type : "GET",
    dataType : "JSON",
    success : function(data){
      $('#modal-form').modal('show');
      $('.modal-title').text('Edit Stock'); 
      $('#id').val(data.id_produk);
      $('#nama').val(data.nama_produk);
      $('#kode').val(data.kode_produk);
      $('#jumlah').val(data.stok);
    },
    error : function(){
      alert("Tidak dapat menampilkan data!");
    }
  });
}

</script>
@endsection