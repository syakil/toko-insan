@extends('layouts.app')

@section('header')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
    <link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('title')
Terima Barang
@endsection

@section('content')
<!-- Content Header (Page header) -->

<!-- Main content -->
<section class="content">
<div class="row">
    <div class="col-md-12">
    <!-- Default box -->
    <div class="box box-solid">
        <div class="box-header with-border">
            @foreach ($no_surat as $no)
            <h3 class="box-title">List Detail Barang <strong> No. {{$no->id_transfer}}&ensp;</strong></h3>
            @endforeach
            
                <input type="submit" class="btn btn-success btn-sm" value="Refresh" onclick="document.location.reload(true)" >

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                title="Collapse">
                <i class="fa fa-minus"></i></button>
            </div>
        </div>
        
        <div class="box-body">
                    <form action="/jurnal/migrasi" method="post">
                    {{ csrf_field() }}
            <table class="table table-bordered" id="tables">
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Kirim</th>
                        <th>Jumlah diterima</th>
                        <th>Tanggal Expired</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($transfer as $p)
                    @if($p->jurnal_status==0)
                    <tr>
                        <td><input type="checkbox" class="form-check-input" id="cek" name="cek[]" value="{{$p->id_pembelian_detail}}"></td>
                        <td>{{$nomer++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->jumlah}}</td>
                        <td>
                        <a href="#" class="diterima" data-type="number" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('api.update',$p->id_pembelian_detail) }}" data-title="masukan jumlah">{{round($p->jumlah_terima)}}</a>
                        </td>
                        <td><a href="#" class="diterima" data-type="text" data-pk="{{$p->id_pembelian_detail}}" data-url="{{ route('api.tanggal',$p->id_pembelian_detail) }}" data-title="masukan jumlah">{{$p->expired_date}}</a>
                        </td>
                        @if ($p->status_lengkap == "Lebih")
                            <td style="background-color: red">{{$p->status_lengkap}}</td>
                        @elseif($p->status_lengkap == "Tidak Lengkap")
                            <td style="background-color: gold">{{$p->status_lengkap}}</td>
                        @elseif($p->status_lengkap == "Lengkap")
                            <td style="background-color:greenyellow">{{$p->status_lengkap}}</td>
                        @else
                            <td>{{$p->status_lengkap}}</td>
                        @endif
                            
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
                <button type="submit" onclick="return confirm('Data Ini Akan di Kirim Ke Jurnal?');" class="pull-right btn btn-danger"><i class="fa fa-send"></i>  Kirim</button>
                </form>

                <!-- <button type="button" id="tombol">Tes sweet alert</button> -->
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>
</div>
</section>

    <!-- /.content -->
@endsection

@section('script')
<!-- <script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>  -->
<script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <!-- x-editable -->
    <script type="text/javascript">
    $.fn.editable.defaults.mode = 'inline';
    </script>
    <script type="text/javascript">
        // editable({
        //     inputclass: 'mytextarea'
        // });
        $(document).ready(function() {
        $('.diterima').editable({
            inputclass: 'some_class'
        });});
    </script>

    <!-- refresh page -->
    <script>
    function reloadpage(){
        location.reload()
    }
    </script>
@endsection