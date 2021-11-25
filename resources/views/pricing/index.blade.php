@extends('layouts.app')

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<link href="//cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css" rel="stylesheet"/>
@endsection

@section('title')
  Pricing
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
            <div class="box-header">
            <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
                Ubah Margin
            </button> -->
            <!-- <a href="{{ route('pricing.tambah') }}" class="btn btn-warning">Tambah Produk</a> -->
            </div>
                <div class="box-body">
                <div class="table-responsive">
                <table class="table table-striped tabel-pricing">
                    <thead>
                        <tr>
                        <th>Nomor</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>HPP</th>
                        <th>Harga Jual Min</th>
                        <th>Harga Jual Member Pabrik</th>
                        <th>Harga Jual Member NI</th>
                        <th>Status</th>
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




<!-- modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Persentas Margin Penjualan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{ route('pricing.margin')}}">
      
        <div class="form-group">
            <label>Fast Moving %</label>
            <input type="number" class="form-control" id="fast" name="fast" value="{{$fast->margin}}">
        </div>
        <div class="form-group">
            <label>Pesanan %</label>
            <input type="number" class="form-control" id="medium" name="medium" value="{{$medium->margin}}">
        </div>
        <div class="form-group">
            <label>Slow Moving %</label>
            <input type="number" class="form-control" id="slow" name="slow" value="{{$slow->margin}}">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

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
       url : "{{ route('pricing.data') }}",
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

