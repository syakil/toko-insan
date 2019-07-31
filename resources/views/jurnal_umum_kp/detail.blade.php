@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  List Detail Barang PO
@endsection

@section('breadcrumb')
   @parent
   <li>detail terima</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body"> 
            <table class="table table-striped" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah PO</th>
                        <th>Jumlah Terima</th>
                        <th>Tanggal Expired <small><i>(yyyy-mm-dd)</i></small></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pembelian as $p)
                    <tr>
                        <td>{{$nomer++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->jumlah}}</td>
                        <td><a href="#" class="edit" data-type="text" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('update.jumlah_terima',$p->id_pembelian_detail)}}" data-title="Masukan Harga">{{$p->jumlah_terima}}</a></td>
                        <td><a href="#" class="tanggal" data-type="combodate" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('update.expired_date',$p->id_pembelian_detail)}}" data-value="{{date('Y-m-d')}}" data-title="Masukan Tanggal">{{$p->expired_date}}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
    </script>
    <script>
    $(document).ready(function(){
    $('#tables').DataTable()
    });
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

<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

    <script>
    // $.fn.editable.defaults.mode = 'inline';
    </script>
@endsection