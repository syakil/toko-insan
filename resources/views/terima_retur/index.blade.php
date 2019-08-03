@extends('layouts.app')

@section('title')
  Terima Barang Retur
@endsection

@section('breadcrumb')
   @parent
   <li>Terima Barang</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body"> 
                    <form action="{{ route('retur.update_status') }}" method="post">
                    {{ csrf_field() }}
            <table class="table table-striped" id="tables">
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>No. Surat Jalan</th>
                        <th>Unit</th>
                        <th>Nama Toko</th>
                        <th>Tanggal</th>
                        <th>Total Item</th>
                        <th>Total Terima</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transfer as $p)
                    @if($p->status == null)
                    <tr>
                        <td><input type="checkbox" name="check[]" value="{{$p->id_pembelian}}"></td>
                        <td>{{$no++}}</td>
                        <td>{{$p->id_pembelian}}</td>
                        <td>{{$p->kode_gudang}}</td>
                        <td>{{$p->nama_toko}}</td>
                        <td>{{tanggal_indonesia(substr($p->created_at, 0, 10), false)}}</td>
                        <td>{{$p->total_item}}</td>
                        <td>{{$p->total_terima}}</td>
                        <td>
                        <a href="{{ route('retur.detail',$p->id_pembelian) }}" class="btn btn-success btn-sm"> <i class="fa fa-eye"></i> </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger pull-right"> <i class="fa fa-send"></i> Proses</button>
            </form>
            </div>
    </div>
  </div>
</div>

    <!-- /.content -->
@endsection
