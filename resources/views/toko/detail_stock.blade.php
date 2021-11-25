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
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            + Stock
        </button>
      </div>
      <div class="box-body"> 
            <table class="table table-striped" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Stock</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @php $no =1 ; @endphp
                    @foreach ($produk as $p)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_produk_detail}}" data-url="/toko/api/update_stock/{{$p->id_produk_detail}}" data-title="Enter username">{{$p->stok_detail}}</a></td>
                        <td><a href="#" class="tanggal" data-type="combodate" data-pk="{{$p->id_produk_detail}}" data-url="/toko/api/update_expired_stock/{{$p->id_produk_detail}}" data-title="Enter username">{{$p->expired_date}}</a></td>
                        <td><a href="{{route('stockToko.delete',$p->id_produk_detail)}}" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

    <!-- /.content -->


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Produk</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('stockToko.store')}}" method="post">
        {{ csrf_field() }} {{ method_field('POST') }}
            <div class="form-group">
                <label for="barcode">Barcode</label>
                <input type="number" class="form-control" id="barcode" name="barcode" value="{{$nama->kode_produk}}" readonly>
            </div>
            <div class="form-group">
                <label for="nama">Nama Produk</label>
                <input type="text" class="form-control" id="nama" name="nama" value="{{$nama->nama_produk}}" readonly>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" id="stock" name="stok" required>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal Kadaluarsa</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
            </div>
      </div>
      <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
      </div>
    </div>
  </div>
</div>


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