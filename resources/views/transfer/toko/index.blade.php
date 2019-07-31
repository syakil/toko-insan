@extends('layouts.app')
@section('title')
KSPPS | NURINSANI
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
            <h3 class="box-title">List Surat Jalan</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                title="Collapse">
                <i class="fa fa-minus"></i></button>
            </div>
        </div>
        
        <div class="box-body">
            <table class="table table-bordered" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>No. Surat Jalan</th>
                        <th>Gudang</th>
                        <th>Unit</th>
                        <th>Tanggal</th>
                        <th>Jumlah Transfer</th>
                        <th>Jumlah diterima</th>
                        <th>Opsi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($dataTransfer as $p)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$p->id_transfer}}</td>
                        <td>{{$p->nama_gudang}}</td>
                        <td>{{$p->nama_toko}}</td>
                        <td>{{$p->tanggal_transfer}}</td>
                        <td>{{$p->total_item}}</td>
                        <td>{{$p->jumlah_terima}}</td>
                        <td>
                        <a href="detail/{{$p->id_transfer}}" class="btn btn-success btn-sm"> <i class="fa fa-eye"></i> </a>
                        <a href="toko/{{$p->id_transfer}}" class="btn btn-danger btn-sm"> <i class="fa fa-print"></i> </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>
</div>
</section>

    <!-- /.content -->
@endsection
