@extends('layouts.app')

@section('title')
  Terima Barang Retur
@endsection

@section('breadcrumb')
   @parent
   <li>Terima Barang</li>
@endsection

@section('content')     
@if ($message = Session::get('error'))
  <script>
    var pesan = "{{$message}}"
    swal("Maaf !", pesan, "error"); 
  </script>
@elseif ($message = Session::get('success'))
  <script>
    var pesan = "{{$message}}"
    swal("Selamat !", pesan, "success"); 
  </script>
@endif

<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body"> 
        <table class="table table-striped" id="tables">
          <thead>
            <tr>
              <th width='1%'>No.</th>
                <th>No. Surat Jalan</th>
                <th>Nama Toko</th>
                <th>Tanggal</th>
                <th>Total Item</th>
                <th>Total Terima</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
          </thead>
          <tbody>
            @foreach ($transfer as $p)
              <tr>
                <td>{{$no++}}</td>
                <td>{{$p->id_pembelian}}</td>               
                <td>{{$p->nama_toko}}</td>
                <td>{{tanggal_indonesia(substr($p->created_at, 0, 10), false)}}</td>
                <td>{{$p->total_item}}</td>
                <td>{{$p->total_terima}}</td>
                @if($p->status == 1)
                    <td><span class="label label-danger">Belum Diterima</span></td>
                    <td><a href="{{ route('retur.detail',$p->id_pembelian) }}" class="btn btn-warning btn-sm"> <i class="fa fa-pencil"></i> </a></td>
                  @elseif($p->status == 'approve')
                    <td><span class="label label-warning">Menunggu Approval</span></td>
                    <td><a disabled class="btn btn-warning btn-sm"> <i class="fa fa-pencil"></i> </a></td>
                  @else
                    <td><span class="label label-success">Sudah Diterima</span></td>
                    <td><a disabled href="{{ route('retur.detail',$p->id_pembelian) }}" class="btn btn-warning btn-sm"> <i class="fa fa-pencil"></i> </a></td>
                  @endif
                
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
