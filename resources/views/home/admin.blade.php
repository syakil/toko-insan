@extends('layouts.app')

@section('title')
  Dashboard
@endsection

@section('breadcrumb')
   @parent
   <li>Dashboard</li>
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
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
            	<h3>{{ $kategori }}</h3>
           		<p>Total Kategori</p>
            </div>
       		<div class="icon">
            	<i class="fa fa-cube"></i>
        	</div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
            	<h3>{{ $produk }}</h3>
           		<p>Total Produk</p>
            </div>
       		<div class="icon">
            	<i class="fa fa-cubes"></i>
        	</div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
            	<h3>{{ $supplier }}</h3>
           		<p>Total Supplier</p>
            </div>
       		<div class="icon">
            	<i class="fa fa-truck"></i>
        	</div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
            	<h3>{{ $member }}</h3>
           		<p>Total Member</p>
            </div>
       		<div class="icon">
            	<i class="fa fa-credit-card"></i>
        	</div>
        </div>
    </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box">

      <div class="box-header with-border">
        <h3 class="box-title">Grafik Pendapatan {{ tanggal_indonesia($awal) }} s/d {{ tanggal_indonesia($akhir) }}</h3>
      </div>

      <div class="box-body">
        <div class="chart">
          <canvas id="salesChart" style="height: 250px;"></canvas>
        </div>
      </div>

    </div>
  </div>



  <a href="#" class="ubah-tanggal" data-toggle="modal" data-target="#exampleModal">
    <div class="col-md-4"></div>  
      <div class="col-md-4 col-sm-6 col-xs-12 text-center">
        <div class="info-box bg-blue">
          <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
          <div class="info-box-content">
          <br>
          <span class="info-box-text">Tanggal Transaksi</span>
          <span class="angka">{{$param_tgl}}</span>
        </div>
        <!-- /.info-box-content -->
      </div>
      <!-- /.info-box -->
    </div>
    <!-- /.col -->
  </a>

</div>

<div class="row">
  
  <div class="col-md-4"></div>
  <div class="col-md-4 col-sm-12 col-xs-12 text-center">
    <a href="{{route('kasa_eod.eod')}}" class="btn btn-primary">Eod</a>
  </div>
  

</div>


<input type="hidden" value="{{$cek}}" id="cek">

<div class="modal" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pengaturan Tanggal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{route('home.store')}}" class="tanggal" method="post">
      {{ csrf_field() }}
        <div class="form-group">
          <label for="tanggal">Tanggal Transaksi</label>
          <input type="date" class="form-control" id="tanggal" name="tanggal" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </form>
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')

<script>
var param ,
param = $("#cek").val();
if (param == 0) {
  $(".modal").modal('show');
}else{
  $(".modal").modal('hide');
}
</script>


<script>
$(".ubah-tanggal").on("click",function(){
  $(".tanggal").attr("action","{{ route('home.update', Auth::user()->id) }}")
})
</script>


<script type="text/javascript">
$(function () {
  var salesChartCanvas = $("#salesChart").get(0).getContext("2d");
  var salesChart = new Chart(salesChartCanvas);
  var salesChartData = {
    labels: {{ json_encode($data_tanggal) }},
    datasets: [
      {
        label: "Electronics",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: {{ json_encode($data_pendapatan) }}
      }
    ]
  };
  var salesChartOptions = {
    pointDot: false,
    responsive: true
  };
  //Create the line chart
  salesChart.Line(salesChartData, salesChartOptions);
});
</script>
@endsection

