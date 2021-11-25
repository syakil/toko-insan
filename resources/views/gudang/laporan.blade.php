@extends('layouts.app')

@section('title')
  Laporan Stok Opname
@endsection

@section('header')

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
@endsection

@section('content')
<div class="table-responsive"> 
<div class="row">
  <div class="col-lg-12">
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
                        <th>Expired Date</th>
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
            'csv', 'excel'
        ],
     "ajax" : {
       "url" : "{{ route('laporanGudang.data') }}",
       "type" : "GET"
     }

   });
   $('div.dataTables_filter input').focus(); 
});
</script>

@endsection