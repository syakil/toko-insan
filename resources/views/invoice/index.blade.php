@extends('layouts.app')

@section('header')

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('title')
  Invoice Pembelian
@endsection

@section('breadcrumb')
   @parent
   <li>Invoice</li>
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
      
      <table class="table table-striped" id="tabel-invoice">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>No. Po</th>
                        <th>Supplier</th>
                        <th>Total Terima</th>
                        <th>Total Invoice</th>
                        <th>Gudang</th>
                        <th>Opsi</th>
                    </tr>
                </thead>

                <tbody>
                    
                </tbody>
            </table>
            </div>
    </div>
  </div>
</div>

    <!-- /.content -->
@endsection

@section('script')

<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

<script>
// $.fn.editable.defaults.mode = 'inline';
$(function(){
    $('.tanggal').editable({
    format: 'YYYY-MM-DD',    
    viewformat: 'YYYY-MM-DD',    
    template: 'D / MMMM / YYYY',    
    combodate: {
            minYear: 2000,
            maxYear: 2030,
            minuteStep: 1
            }
    });
    });
</script>

<script>

$(function(){
    $('.status').editable({
        value: 2,    
        source: [
              {value: 0, text: 'Pilih'},
              {value: 1, text: 'TOP'},
              {value: 2, text: 'CASH'}
           ]
    });
});
</script>

<script>    
$(document).ready(function(){
    $('#tabel-invoice').DataTable({
        
        "ajax" :{
            url     : "{{route('invoice.data')}}",
            type    : "GET"
        }
        
    })
});
</script>

<script>

function detail(){
    
}

</script>
@endsection
