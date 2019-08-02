@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  Pricing
@endsection

@section('breadcrumb')
   @parent
   <li>pricing</li>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
                Ubah Margin
            </button>

            </div>
                <div class="box-body">
                <table class="table table-striped tabel-supplier" id="tables">
                    <thead>
                        <tr>
                        <th>Nomor</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>HPP</th>
                        <th>Harga Jual Min</th>
                        <th>Harga Jual Member Pabrik</th>
                        <th>Harga Jual Member NI</th>
                        <th>Status</th>
                        <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produk as $data)
                        <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $data->kode_produk }}</td>
                        <td>{{ $data->nama_produk }}</td>
                        <td>{{ $data->harga_beli }}</td>
                        <td>{{ round($data->harga_beli + ($data->harga_beli*$data->margin/100)) }}</th>
                        <td>{{ $data->harga_jual }}</td>
                        <td>{{ $data->harga_jual_insan}}</td>
                        <td>{{ $data->keterangan }}</td>
                        <td><a href="{{ route('pricing.edit',$data->kode_produk)}}"class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
        </div>
    </div>
</div>

    <!-- /.content -->




<!-- modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Persentas Margin Penjualan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{ route('pricing.margin')}}">
      
        <div class="form-group">
            <label>Fast Moving %</label>
            <input type="number" class="form-control" id="fast" name="fast" value="{{$fast->margin}}">
        </div>
        <div class="form-group">
            <label>Medium Moving %</label>
            <input type="number" class="form-control" id="medium" name="medium" value="{{$medium->margin}}">
        </div>
        <div class="form-group">
            <label>Slow Moving %</label>
            <input type="number" class="form-control" id="slow" name="slow" value="{{$slow->margin}}">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>
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