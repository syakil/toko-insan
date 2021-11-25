@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  List Stok Opname Parsial
@endsection

@section('breadcrumb')
   @parent
   <li>stok opname parsial</li>
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

<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body">
        <form action="{{ route('stok_opname_parsial.store') }}" method="post">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{Auth::user()->unit}}">
          <table class="table table-bordered table-detail">
            <thead>
              <tr>
                <th width='1%'>No.</th>
                <th width='1%'>Barcode</th>
                <th width='20%'>Nama Barang</th>
                <th>Jumlah System</th>
                <th>Jumlah Fisik</thwidth='1%'>
              </tr>
            </thead>
            <tbody>
              @foreach ($data_produk as $p)
                <tr>
                  <td>{{$nomer++}}</td>
                  <td>{{$p->kode_produk}}</td>
                  <td>{{$p->nama_produk}}</td>
                  <td>{{$p->stok}}</td>
                  <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_produk_so}}" data-url="{{ route('stok_opname_parsial.update',$p->id_produk_so)}}" data-title="Masukan Qty">{{$p->qty}}</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
      </div>
      <div class="box-footer">
        <button type="submit" class="btn btn-primary pull-right" style="margin-left:10px;">Proses</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
    $('.edit').editable();
  }); 
</script>
    

<script>
var table;
$(function(){
  $('.tables').DataTable();
  table = $('.table-detail').DataTable({
    "scrollY" : '50vh',
    "scrollCollapse": true,
    "searching": false,
    "paging" : false,
    "info":     false
  })
})
</script>

<script>
  // $.fn.editable.defaults.mode = 'inline';
$(function(){
  $('.tanggal').editable({
    format: 'YYYY-MM-DD',    
    viewformat: 'YYYY-MM-DD',    
    template: 'D / MMMM / YYYY',    
    combodate: {
      minYear: 2018,
      maxYear: 2030,
      minuteStep: 1
    }
  });
});
</script>

<script>
  $.fn.editable.defaults.mode = 'inline';
</script>
@endsection
