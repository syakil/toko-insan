@extends('layouts.app')

@section('header')
  <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  List Barang 
@endsection

@section('breadcrumb')
  @parent
  <li>detail terima</li>
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
        <table class="table table-striped table-detail">
          <thead>
            <tr>
              <th width='1%'>No.</th>
              <th>Barcode</th>
              <th>Nama Barang</th>
              <th>Jumlah Kirim<njhggkjg/th>
              <th>Jumlah Terima</th>
              <th>Tanggal Expired <small><i>(yyyy-mm-dd)</i></small></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($kirim as $p)
            <tr>
              <td>{{$nomer++}}</td>
              <td>{{$p->kode_produk}}</td>
              <td>{{$p->nama_produk}}</td>
              <td>{{$p->jumlah}}</td>
              <td><a href="#" class="edit" data-type="text" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('updatetoko.jumlah_terima',$p->id_pembelian_detail)}}" data-title="Masukan Jumlah Qty" tabindex="1">{{$p->jumlah_terima}}</a></td>
              <td><a href="#" class="tanggal" data-type="combodate" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('updatetoko.expired_date',$p->id_pembelian_detail)}}" data-title="Masukan Harga">{{$p->expired_date}}</a></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="box-footer">
        <form action="{{route('terima_antar_gudang.terima')}}" method="POST">
          {{csrf_field()}}
          <input type="hidden" name="id" value="{{$nopo->id_pembelian}}">
          <button type="submit" class="btn btn-danger pull-right"> <i class="fa fa-send"></i> Proses</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- /.content -->
@section('script')

<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script type="text/javascript">
  
  $(document).ready(function() {
    $('.edit').editable();
  });

  var table;
  $(function(){
    $('.tabels').DataTable();
    table = $('.table-detail').DataTable({
      "dom" : 'Brt',
      "bSort" : true,
      "processing" : true,
      "scrollY" : "500px",
      "paging" : false,   
      "scrollCollapse": true, 
    });
  });
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
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>
@endsection
