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
            <form action="{{ route('pembelian.simpan') }}" method="post">
            {{ csrf_field() }}
            <table class="table table-bordered" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>@ Satuan</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pembelian_detail as $p)
                    <tr>
                        <input type="hidden" name="id" value="{{$p->id_pembelian}}">
                        <td>{{$nomer++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->jumlah}}</td>
                        <td>{{$p->satuan}}</td>
                        <td>{{$p->isi_satuan}}</td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('ubah.harga',$p->id_pembelian_detail)}}" data-title="Masukan Harga">{{$p->sub_total_terima}}</a></td>        
                    </tr>
                    @endforeach
                </tbody>
            </table>
                <button type="submit" class="btn btn-danger">Simpan</button>
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
    </script>
    <script>
    $(document).ready(function(){
    $('#tables').DataTable()
    });
    </script>
    <script>
    $.fn.editable.defaults.mode = 'inline';
    </script>

<script>
// function harga(e) {
//     var sub_total = $(".harga").val();
//     var qty = $(".qty").val();
//     var satuan = sub_total/qty;
//     $(".satuan").val(Math.round(satuan
//     ));
// }
</script>
@endsection