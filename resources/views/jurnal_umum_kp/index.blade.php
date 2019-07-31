@extends('layouts.app')

@section('title')
  Jurnal Umum
@endsection

@section('header')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('breadcrumb')
   @parent
   <li>jurnal umum</li>
@endsection

@section('content')     
@if ($message = Session::get('error'))
      <div class="alert alert-danger alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button> 
        <strong>{{ $message }}</strong>
      </div>
    @endif

<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      <h3 class="box-title">Input Jurnal Baru</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            </div>
      </div>

      <div class="box-body"> 
    <div class="col-md-3"></div>
    <div class="col-md-6 justify-content-center">
      <form action="{{ route('jurnal_umum_kp.create')}}" method="post">
      {{ csrf_field() }}
      <!-- kode Transaksi -->
        <div class="form-group">
          <label for="kode_transaksi">Kode Transaksi</label>
          <input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi" value="{{$kode_transaksi}}" readonly>
        </div>
      <!-- tanggal jurnal -->
        <div class="form-group">
          <label for="tanggal_jurnal">Tanggal Jurnal</label>
          <input type="date" class="form-control" id="tanggal_jurnal" name="tanggal_jurnal" data-date-format="DD MMMM YYYY">
          <p><small><i><font color="red">{{ $errors->first('tanggal_jurnal') }}</font></i></small></p>
        </div>
      <!-- keterangan transaksi -->
        <div class="form-group">
          <label for="keterangan">Keterangan Jurnal</label>
          <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{old('keterangan')}}">
          <p><small><i><font color="red">{{ $errors->first('keterangan') }}</font></i></small></p>
        </div>
      <!-- rekening -->
        <div class="form-group">
          <label for="rekening">Rekening </label>
          <select class="form-control js-example-basic-multiple" name="rekening">
            @foreach($rekening as $coa)
            <option value="{{$coa->kode_rek}}">{{$coa->kode_rek}} - {{$coa->nama_rek}}</option>
            @endforeach
          </select>
          <!-- <input type="number" class="form-control" id="rekening" name="rekening"> -->
          <!-- <div id="rekening_list"></div> -->
        </div>
      <!-- transaksi -->
        <div class="form-group">
          <label for="transaksi">Transaksi </label>
          <select class="form-control" name="transaksi" id="transaksi">
            <option value="debit">Debit</option>
            <option value="kredit">Kredit</option>
          </select>
        </div>
      <!-- nominal -->
      <div class="form-group">
          <label for="nominal">Nominal </label>
          <input type="number" class="form-control" id="nominal" name="nominal">
          <p><small><i><font color="red">{{ $errors->first('nominal') }}</font></i></small></p>
        </div>
      
        <button type="submit" class="btn btn-primary">Simpan</button>
      </form>
      </div>
      </div>


    </div>
  </div>
</div>
<!-- list detail -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>

      <div class="box-body"> 
      <table class="table table-striped" id="tables">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Rekening</th>
                        <th>Keterangan</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($list_jurnal as $p)
                    <tr>
                        <td>{{$nomer++}}</td>
                        <td>{{$p->kode_rekening}}</td>
                        <td>{{$p->keterangan_transaksi}}</td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_transaksi}}" data-url="{{ route('updatejurnal.debet',$p->id_transaksi)}}" data-title="Masukan Nominal" >{{$p->debet}}</a></td>
                        <td><a href="#" class="edit" data-type="number" data-pk="{{$p->id_transaksi}}" data-url="{{ route('updatejurnal.kredit',$p->id_transaksi)}}" data-title="Masukan Nominal">{{$p->kredit}}</a></td>
                        <td><a href="{{ route('jurnal_umum_kp.destroy',$p->id_transaksi)}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a></td>
                        
                    </tr>
                    @endforeach
                    </tbody>
                    <tbody>  
                    <tr>
                    <td></td>
                    <td></td>
                    <td><strong><i>Total</i></strong></td>
                    <td><strong><i>{{$debet}}</i></strong></td>
                    <td><strong><i>{{$kredit}}</i></strong></td>
                    @if($debet == $kredit)
                    <td><font color="green">Balance</font></td>
                    @else
                    <td><font color="red">Not Balance</font></td>
                    @endif
                    </tr>
                </tbody>
            </table>
            @if($debet == $kredit && $debet != 0)
            <a href="{{ route('jurnal_umum_kp.approve')}}" class="btn btn-danger pull-right"><i class="fa fa-legal"> </i> Selesai</a>
            @endif
      </div>


    </div>
  </div>
</div>

    <!-- /.content -->
@endsection

@section('script')

    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
        $('.edit').editable();
    });
    </script>
    <!-- combodate xeditable -->
<script src="https://momentjs.com/downloads/moment-with-locales.js"></script>

<!-- select 2 -->

<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.js-example-basic-multiple').select2();
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
              {value: 1, text: 'TOP'},
              {value: 2, text: 'CASH'}
           ]
    });
});
</script>
@endsection
