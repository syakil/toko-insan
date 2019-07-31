@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  List Barang PO
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
            <table class="table table-bordered" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pembelian as $p)
                    <tr>
                        <td>{{$nomer++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->jumlah}}</td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('ubah.harga',$p->id_pembelian_detail)}}" data-title="Masukan Harga">{{$p->harga_beli}}</a></td>                            
                    </tr>
                    @endforeach
                </tbody>
            </table>
                <a href="{{ route('pembelian.jurnal',$id_pembelian) }}" class="btn btn-danger">Simpan</a>
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
    $.fn.editable.defaults.mode = 'inline';
    </script>
@endsection