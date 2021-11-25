@extends('layouts.app')

@section('title')
  Stok Opname
@endsection

@section('breadcrumb')
   @parent
   <li>stok_opname</li>
@endsection


@section('header')

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadmodal">Upload File</button>
            <a href="{{route('stock_opname.export_excel')}}" type="button" class="btn btn-danger" >Download Contoh File</a>
            </div>  
            <!-- Modal -->
            <div class="modal fade" id="uploadmodal" tabindex="-1" role="dialog" aria-labelledby="uploadmodal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadmodal">Upload File Stok Opname</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('stock_opname.import_excel')}}" enctype="multipart/form-data">

                    {{csrf_field()}}
                        <div class="form-group">
                            <label for="import_excel">Upload File</label>
                            <input type="file" class="form-control-file" name ="import_excel" id="import_excel">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
                </div>
            </div>
            </div>

            <div class="box-body">
                <table class="table table-striped tabel-stock" >
                    <thead>
                        <tr>
                            <th width='1%'>No.</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Stok Opname</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
                    
                </table>
            </div>

            <div class="box-footer">
                <form action="{{route('stock_opname.proses')}}" method="post">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger pull-right">Proses</button>
                </form>
            </div>
        
        </div>
    </div>
</div>

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
       "url" : "{{ route('stock_opname.data') }}",
       "type" : "GET"
     }
   });
   $('div.dataTables_filter input').focus(); 
});
</script>

@endsection
