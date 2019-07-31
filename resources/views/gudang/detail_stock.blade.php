@extends('layouts.app')

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
                        <td>{{$p->stok_detail}}</td>
                        <td>{{tanggal_indonesia(substr($p->expired_date, 0, 10), false)}}</td>
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

@endsection