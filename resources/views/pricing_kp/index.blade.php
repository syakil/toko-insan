@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  Barang di Terima
@endsection

@section('breadcrumb')
   @parent
   <li>Barang diterima</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      
      @if ($message = Session::get('berhasil'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                <strong>{{ $message }}</strong>
            </div>
        @endif

      </div>
      <div class="box-body"> 
      
      <form action="#" method="post">
                    {{ csrf_field() }}
                    
            <table class="table table-striped" id="tabel-pricing">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>No. Po</th>
                        <th>Tanggal</th>
                        <th>Unit</th>
                        <th>Supplier</th>
                        <th>Total Harga</th>
                        <th>Opsi</th>
                    </tr>
                </thead>

                <tbody>
                    
                </tbody>
            </table>
            </form>
            </div>
    </div>
  </div>
</div>

    <!-- /.content -->
@endsection

@section('script')

<script>
    $(document).ready(function(){
    $('#tabel-pricing').DataTable({
        "serverside" : true,
        "dom" : 'Bfrtip',
        "ajax" :{
            url : "{{ route('pricing_kp.data') }}",   
            type : "GET"
            }
        })
    });
</script>
@endsection
