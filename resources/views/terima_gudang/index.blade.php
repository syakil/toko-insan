@extends('layouts.app')

@section('title')
  Terima Barang
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

<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body">    
        <table class="table table-striped" id="tables">
          <thead>
            <tr>
              <th>No.</th>
              <th>No. Surat Jalan</th>
              <th>Tanggal</th>
              <th>Total Item</th>
              <th>Total Terima</th>
              <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($terima as $p)  
            <tr>
              <td>{{$no++}}</td>
              <td>{{$p->id_pembelian}}</td>
              <td>{{tanggal_indonesia(substr($p->created_at, 0, 10), false)}}</td>
              <td>{{$p->total_item}}</td>
              <td>{{($p->total_terima)}}</td>
              <td>
                <a href="{{ route('terima_antar_gudang.detail',$p->id_pembelian) }}" class="btn btn-success btn-sm"> <i class="fa fa-eye"></i> </a>
              </td>
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
