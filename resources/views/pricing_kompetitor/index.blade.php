@extends('layouts.app')

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<link href="//cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css" rel="stylesheet"/>
@endsection

@section('title')
  Pricing Harga Pasar
@endsection

@section('breadcrumb')
   @parent
   <li>pricing</li>
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
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-striped tabel-pricing">
            <thead>
              <tr>
                <th>Nomor</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Harga Jual Toko Insan</th>
                <th>Harga Jual Indo</th>
                <th>Harga Jual Alfa</th>
                <th>Harga Jual Olshop</th>
                <th>Harga Jual Grosir</th>
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
</div>

<!-- /.content -->
@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
  table = $('.tabel-pricing').DataTable({     
    "serverside" : true,
    "dom": 'Bfrtip',
      buttons: [
        'excel'
      ],
    ajax : {
      url : "{{ route('pricing_kompetitor.data') }}",
      type : "GET"
    }
  });
    $('div.dataTables_filter input').focus(); 
});
</script>

<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script> 
@endsection

