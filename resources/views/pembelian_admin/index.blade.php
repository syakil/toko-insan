@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  Barang di Terima
@endsection

@section('breadcrumb')
   @parent
   <li>Barang diterima</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body"> 
      
      <form action="{{ route('pembelian.update_jurnal') }}" method="post">
                    {{ csrf_field() }}
            <table class="table table-striped" id="tables">
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>No. Po</th>
                        <th>Unit</th>
                        <th>Supplier</th>
                        <th>Tanggal</th>
                        <th>Total Harga</th>
                        <th>Jatuh Tempo <small>(yyyy-mm-dd)</small></th>
                        <th width='1%'>Jenis</th>
                        <th>Opsi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pembelian as $p)
                    <tr>
                        <td>
                        <input class="flat-red" type="checkbox" name="check[]" value="{{$p->id_pembelian}}">
                        </td>
                        <td>{{$no++}}</td>
                        <td>{{$p->id_pembelian}}</td>
                        <td>{{$p->kode_gudang}}</td>
                        <td>{{$p->nama}}</td>
                        <td>{{ tanggal_indonesia(substr($p->created_at, 0, 10), false)}}</td>
                        <td>{{$p->total_harga_terima}}</td>
                        <td>
                        <!-- <a href="#" class="tanggal" data-type="combodate" data-pk="1" data-url="/post" data-value="{{date('Y-m-d')}}" data-title="Select date">{{$p->jatuh_tempo}}</a> -->
                        <a href="#" class="tanggal" data-type="combodate" data-pk="{{$p->id_pembelian}}" data-url="{{ route('ubah.jatuh_tempo',$p->id_pembelian)}}" data-value="{{date('Y-m-d')}}" data-title="Masukan Tanggal"></a>
                        </td>
                        <td>
                        <a href="#" class="status" data-type="select" data-pk="{{$p->id_pembelian}}" data-url="{{ route('ubah.tipe_bayar',$p->id_pembelian)}}" data-title="Select status" data-value="{{$p->tipe_bayar}}"></a></td>
                        <td>
                        <a href="{{ route('pembelian.admin_detail',$p->id_pembelian) }}" class="btn btn-warning btn-sm"> <i class="fa fa-pencil"></i> </a>
                        <a href="{{ route('pembelian.cetak_po',$p->id_pembelian) }}" target="_blank" class="btn btn-primary btn-sm">PO</a>
                        <a href="{{ route('pembelian.cetak_fpd',$p->id_pembelian) }}" target="_blank" class="btn btn-danger btn-sm">FPD</i></a>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger pull-right"><i class="fa fa-legal"></i> Approve</button>
            </form>
            </div>
    </div>
  </div>
</div>

    <!-- /.content -->
@endsection

@section('script')

    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
        $('.edit').editable();
    });
    </script>
    
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

    <script>
    // $.fn.editable.defaults.mode = 'inline';
    $(function(){
      $('.tanggal').editable({
        format: 'YYYY-MM-DD',    
        viewformat: 'YYYY-MM-DD',    
        template: 'D / MMMM / YYYY',    
        combodate: {
                minYear: 2000,
                maxYear: 2030,
                minuteStep: 1
                }
        });
      });
    </script>

<script>
$(function(){
    $('.status').editable({
        value: 2,    
        source: [
              {value: 1, text: 'TOP'},
              {value: 2, text: 'CASH'}
           ]
    });
});
</script>
@endsection