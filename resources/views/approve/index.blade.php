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
                <form action="{{ route('approve.store') }}" method="post">
                {{ csrf_field() }}
            <table class="table table-striped" id="tables">
            <input type="checkbox" name="select-all" id="select-all" class="checkbox"> Pilih Semua
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>Unit</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Stock</th>
                        <th>Tanggal Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody>
                @php $no =1 ; @endphp
                    @foreach ($produk as $p)
                    <tr>
                        <td><input type="checkbox" name="kode[]" id="kode" value="{{$p->id_produk_detail}}" ></td>
                        <td>{{$no++}}</td>
                        <td>{{$p->unit}}</td>
                        <td>{{$p->kode_produk}}</td>
                        <td>{{$p->nama_produk}}</td>
                        <td>{{$p->stok_detail}}</td>
                        <td>{{$p->expired_date}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
                <button type="submit" class="btn btn-danger">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- /.content -->

@endsection

@section('script')

<script language="JavaScript">
$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;                        
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;                       
        });
    }
});
</script>

@endsection