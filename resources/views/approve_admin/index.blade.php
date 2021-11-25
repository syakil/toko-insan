@extends('layouts.app')

@section('title')
  Daftar Produk
@endsection

@section('header')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
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
                <form action="{{ route('approve_admin.store') }}" method="post">
                {{ csrf_field() }}
            <table class="table table-striped tabel-so">
            <input type="checkbox" name="select-all" id="select-all" class="checkbox"> Pilih Semua
                <thead>
                    <tr>
                        <th width='1%'></th>
                        <th width='1%'>No.</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Tanggal SO</th>
                        <th>Stock Sistem</th>
                        <th>Stock Sebenarnya</th>
                        <th>Nama Gudang</th>
                    </tr>
                </thead>
                <tbody>
                
                </tbody>
            </table>
                <button type="submit" class="btn btn-danger pull-right approve" disabled>Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- /.content -->

@endsection

@section('script')


<script>

var table;
    $(function(){
   table = $('.tabel-so').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('approve_admin.data') }}",
       "type" : "GET"
     }
   });
});


</script>

<script language="JavaScript">
$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;
            $(".approve").attr("disabled",false);                        
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;
            $(".approve").attr("disabled",true);                       
        });
    }
});
</script>



<script type="text/javascript">

var n;
function check(){
    n = $("input:checked").length;

    if (n >= 1) {
        $(".approve").attr("disabled",false);
    }else{
        $(".approve").attr("disabled",true);
    }
}


</script>



@endsection