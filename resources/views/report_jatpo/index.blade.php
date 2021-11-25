@extends('layouts.app')

@section('title')
  Daftar Pembayaran Jatuh Tempo
@endsection

@section('header')

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

@endsection

@section('breadcrumb')
   @parent
   <li>pembayaran</li>
@endsection

@section('content')

<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body"> 
            <table class="table table-striped tabel-laporan" >
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Nama Supplier</th>
                        <th>No Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Nama Bank</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
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

    <!-- /.content -->

@include('report_jatpo.detail')

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

   table = $('.tabel-laporan').DataTable({

     "processing" : true,
     "serverside" : true,
     dom: 'Bfrtip',
     buttons: ['excel'],
     "ajax" : {
        "url" : "{{ route('report_jatpo.data') }}",
        "type" : "GET"
     }

   });
   $('div.dataTables_filter input').focus(); 
});
</script>

<script>
function showDetail(id){
    $('#modal-detail').modal('show');

    table = $('.tabel-detail').DataTable({

        "processing" : true,
        "serverside" : true,
        dom: 'Bfrtip',
        "ajax" : {
        "url" : "report_jatpo/"+id+"/detail",
        "type" : "GET"
        }

    });
    console.log(id);
    // table1.ajax.url("report_jatpo/"+id+"/detail");
    table1.ajax.reload();
}
</script>

@endsection