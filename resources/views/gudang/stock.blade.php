@extends('layouts.app')

@section('title')
  Daftar Produk
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
                        <th>Stock</th>
                        <th>Stock Min</th>
                    </tr>
                </thead>

                <tbody>
                @php $no =1 ; @endphp
                    @foreach ($produk as $p)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td> <a href="{{ route('stock.detail',$p->kode_produk)}}"> {{$p->nama_produk}}</a></td>
                        <td>{{$p->stok}}</td>
                        <td>{{$p->stok_min}}</td>
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