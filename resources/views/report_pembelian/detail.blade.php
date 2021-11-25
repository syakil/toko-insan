@extends('layouts.app')

@section('title')
  Detail Pembelian No.{{$id}}
@endsection

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<link href="//cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css" rel="stylesheet"/>
@endsection



@section('breadcrumb')
   @parent
   <li>pembelian</li>
@endsection

@section('header')
>
@endsection

@section('content')
<div class="row">
<div class="col-xs-12">
  <div class="box">
    <div class="box-body">  

<table class="table table-striped tabel-penjualan">
<thead>
  <tr>
      <th width="30">No</th>
      <th>Barcode</th>
      <th>Nama Produk</th>
      <th>Harga Satuan</th>
      <th>Jumlah/Qty</th>
      <th>Total Harga</th>
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
$(document).ready( function () {
   table = $('.tabel-penjualan').DataTable({
      "processing" : true,
      "serverside" : true,
      "scrollX":"100%",
      "dom": 'Bfrtip',
        buttons: [
            'excel'
        ],
      "ajax" : {
      "url" : "{{route('report_pembelian.data_detail',$id)}}",
      "type" : "GET"
     }
   }); 
});
</script>

<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script> 



@endsection
