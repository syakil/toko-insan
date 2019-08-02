@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  Daftar Detail Produk
@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
@endsection

@section('content')

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
                        <th>Nama Produk</th>
                        <th>Harga Beli</th>
                        <th>Stock</th>
                        <th>Tanggal Kadaluarsa</th>
                    </tr>
                </thead>

                <tbody>
                @php $no =1 ; @endphp
                    @foreach ($produk as $p)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->harga_beli}}</td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_produk_detail}}" data-url="{{ route('updateStock.stock',$p->id_produk_detail)}}" data-title="Masukan Qty">{{$p->stok_detail}}</a></td>
                        <td><a href="#" class="tanggal" data-type="combodate" data-pk="{{$p->id_produk_detail}}" data-url="{{ route('updateStock.expired_date',$p->id_produk_detail)}}" data-value="{{date('Y-m-d')}}" data-title="Masukan Tanggal">{{$p->expired_date}}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
    $.fn.editable.defaults.mode = 'inline';
    </script>

@endsection