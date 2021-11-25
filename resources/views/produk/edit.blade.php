@extends('layouts.app')

@section('title')
  Edit Produk
@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
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
      <div class="box-header">
        
      <form class="form-horizontal" id="form_produk" action="{{route('produk.update')}}" method="post">
      {{ csrf_field() }} {{ method_field('POST') }}

        <input type="hidden" name="id" value="{{$produk->id_produk}}">
        
        <div class="modal-body"> 
          <div class="form-group">
            <label for="kode" class="col-md-3 control-label">Kode Produk</label>
            <div class="col-md-6">
              <input id="kode" type="number" class="form-control" value="{{$produk->kode_produk}}" readonly name="kode" autofocus required>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="nama" class="col-md-3 control-label">Nama Produk</label>
            <div class="col-md-6">
              <input id="nama" type="text" value="{{$produk->nama_produk}}"  class="form-control" name="nama" required>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="nama_struk" class="col-md-3 control-label">Nama Struk</label>
            <div class="col-md-6">
              <input id="nama_struk" type="text" class="form-control" name="nama_struk" value="{{$produk->nama_struk}}"  required>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="status" class="col-md-3 control-label">Satuan</label>
            <div class="col-md-6">
              <select id="status" type="text" class="form-control" name="status" required>
                <option value="" disabled> -- Pilih Satuan-- </option>
                <option value="Pcs">Pcs</option>
                <option value="Kg">Kg</option>
                <option value="Grm">Grm</option>
                <option value="Liter">Liter</option>
                <option value="Pack">Pack</option>
                <option value="Box/Dus">Box/Dus</option>
                </select>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="status" class="col-md-3 control-label">Status</label>
            <div class="col-md-6">
              <select id="status" type="text" class="form-control" name="status" required>
                <option value="" disabled> -- Pilih Status-- </option>
                @foreach($status as $list)
                  @if($list->id_status == $produk->param_status)
                    <option value="{{ $list->id_status }}" selected>{{ $list->keterangan }}
                  @else
                <option value="{{ $list->id_status }}">{{ $list->keterangan }}
                </option>
                @endif
                @endforeach
              </select>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="supplier" class="col-md-3 control-label">Supplier</label>
            <div class="col-md-6">
              <select id="supplier" type="text" class="form-control" name="supplier" required>
                <option value=""disabled> -- Pilih Supplier-- </option>
                @foreach($supplier as $list)
                @if($list->id_supplier == $produk->id_supplier)
                <option value="{{ $list->id_supplier }}" selected>{{ $list->nama }}</option>
                @else
                <option value="{{ $list->id_supplier }}">{{ $list->nama }}</option>
                @endif
                @endforeach
              </select>
              <span class="help-block with-errors"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="kategori" class="col-md-3 control-label">Kategori</label>
            <div class="col-md-6">
              <select id="kategori" type="text" class="form-control" name="kategori" required>
                <option value=""disabled> -- Pilih Kategori-- </option>
                @foreach($kategori as $list)
                @if($list->id_kategori == $produk->id_kategori)
                <option value="{{ $list->id_kategori}}" selected>{{ $list->nama_kategori }}</option>
                @else
                <option value="{{ $list->id_kategori }}" selected>{{ $list->nama_kategori }}</option>
                @endif
                @endforeach
              </select>
              <span class="help-block with-errors"></span>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-save"><i class="fa fa-floppy-o"></i> Simpan </button>
          <a href="{{route('produk.index')}}" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</a>
        </div>
    
        </form>      
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')
<script>
function hitung2() {
    var competitor1 = $("#competitor1").val();
    var competitor2 = $("#competitor2").val();
    var competitor3 = $("#competitor3").val();
    var avg = (parseInt(competitor1)+parseInt(competitor2)+parseInt(competitor3))/3;
    $("#avg").val(Math.round(avg));
}
</script>
@endsection