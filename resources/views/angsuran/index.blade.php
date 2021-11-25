@extends('layouts.app')

@section('title')
  Transaksi Angsuran
@endsection

@section('breadcrumb')
   @parent
   <li>angsuran</li>
@endsection

@section('header')

@endsection

@section('content')     
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-body">
                <div class="col-md-offset-4">
                    <div class="col-md-5">
                    <form action="" class="form">

                        <div class="form-group pilih-transaksi">
                            <select class="form-control" id="jenis_transaksi">
                                <option selected disabled>Pilih Transaksi</option>
                                <option value="individu">Individu</option>
                                <option value="kelompok">Kelompok</option>
                            </select>
                        </div>
                        
                        
                        <div class="kelompok col-md">
                        
                        <form class="form-kelompok" method="post">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <input type="text" class="form-control" rabindex="1" id="kode_kelompok" name="kode_kelompok" placeholder="Kode Kelompok">
                                <span class="input-group-btn">
                                    <button id="kelompok-show" onclick="showKelompok()" type="button" class="btn btn-info">...</button>
                                </span>
                            </div>
                        
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="text" tabindex="2" class="form-control" id="nama_kelompok" name="nama_kelompok" placeholder="Nama Kelompok">
                            </div>

                            <br>
                            <button type="button" disabled class="btn btn-primary pull-right"><i class="fa fa-search"> </i> Cari</button>

                        </form>
                        </div>


                        <div class="individu col-md">
                        
                        <form class="form-individu" method="post">
                        {{csrf_field()}}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="hidden" name="id" id="id">
                                <!-- <input type="text" name="nominal_titipan" id=nominal_titipan> -->
                                <!-- <input type="hidden" name="nominal_angsuran" id=nominal_angsuran>
                                <input type="hidden" name="nominal_os" id=nominal_os> -->
                                <input type="text" tabindex="3" class="form-control" id="id_member" name="id_member" placeholder="ID Member">
                                <span class="input-group-btn">
                                    <button id="member-show" onclick="showMember()" type="button" class="btn btn-info show-produk">...</button>
                                </span>
                            </div>
                            <small id="emailHelp" class="form-text text-muted"><i> (ID_Member)</i></small>

                            <div class="input-group">
                                <select class="form-control" style="padding-right: 250px;" name="keterangan_transaksi" id="keterangan_transaksi">
                                    <option value="0" selected disabled>Keterangan Transaksi</option>
                                    <!-- <option value="setoran">Setoran Angsuran</option> -->
                                    <option value="kurang">Setoran Kurang Angsuran Ke Titipan</option>
                                    <option value="titipan">Setorang Angsuran Dari Titipan</option>
                                    <option value="pelunasan">Pelunasan</option>
                                    <!-- <option value="pabrik">Pelunasan Pabrik</option> -->
                                </select>
                            </div>
                            <small id="emailHelp" class="form-text text-muted"><i> (Keterangan Transaksi)</i></small>

                            <div class="kolom_nominal">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="number" tabindex="4" class="form-control" id="nominal" disabled name="nominal" placeholder="Setoran">
                            </div>
                                <small id="emailHelp" class="form-text text-muted"><i> (Jumlah Setoran)</i></small>
                            </div>

                                <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="number" tabindex="4" class="form-control" id="nominal_os" disabled name="nominal_os" placeholder="Outstanding">
                            </div>
                                <small id="emailHelp" class="form-text text-muted"><i> (Jumlah OS)</i></small>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="number" tabindex="4" class="form-control" id="nominal_angsuran" disabled name="nominal_angsuran" placeholder="Angsuran">
                            </div>
                                <small id="emailHelp" class="form-text text-muted"><i> (Jumlah Angsuran)</i></small>   

                            
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="number" tabindex="4" class="form-control" id="nominal_titipan" disabled name="nominal_titipan" placeholder="Titipan">
                            </div>
                            <small id="emailHelp" class="form-text text-muted"><i> (Jumlah Titipan)</i></small>
                                                    
                            <br>
                            <button type="button" onclick="addTransaksi();" disabled class="btn btn-success pull-right input-transaksi">Input</button>

                        </form>

                        </div> 
                        
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tabel-transaksi">
<div class="row">
    <div class="col-xs-12">
        <div class="box">    
            <div class="box-body box-detail">
                <table class="table table-striped tabel-detail" >
                    <thead>
                        <tr>
                            <th width='1%'>No.</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
                    <button class="btn btn-success selesai pull-right">Selesai</button>
            </div>
        </div>
    </div>
</div>
</div>



<div class="tabel-transaksi-kelompok">
<div class="row">
    <div class="col-xs-12">
        <div class="box">    
            <div class="box-header">
                <input type="checkbox" name="select-all" id="select-all" class="checkbox"> Pilih Semua
            </div>
            <div class="box-body box-detail">
            <form action="{{route('angsuran.store_kelompok')}}" method="post">
            {{csrf_field()}}
                <table class="table table-striped tabel-detail-kelompok" >
                    <thead>
                        <tr>
                            <th width='1%'>Pilih.</th>
                            <th>ID Member</th>
                            <th>Nama</th>
                            <th>Unit</th>
                            <th>Kelompok</th>
                            <th>OS</th>
                            <th>Margin</th>
                            <th>Setoran</th>
                            <th>Angsuran</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary pull-right proses" disabled>Proses</button>
            </form>
            </div>
        </div>
    </div>
</div>
</div>

@include('angsuran.member')
@include('angsuran.kelompok')
@endsection

@section('script')
<script>
$('.kelompok').hide();
$('.individu').hide();
$('.tabel-transaksi').hide();
$('.tabel-transaksi-kelompok').hide();

$('#jenis_transaksi').change(function(){
    var jenis = $(this).val();

    if (jenis == "individu") {
        $('.kelompok').hide();
        $('.individu').show();
        $('.tabel-transaksi-kelompok').hide();
    }else{
        $('.tabel-transaksi').hide();
        $('.individu').hide();
        $('.kelompok').show();

    }
});
</script>


<script>
$('#keterangan_transaksi').change(function(){
    var jenis = $(this).val();
    titipan = $('#nominal_titipan').val();


    if (jenis == "pelunasan" || jenis == 'kurang') {
        $('.kolom_nominal').show();
        $('#nominal').attr('disabled',false);
    }else{
        $('#nominal').attr('disabled',true);
        $('.kolom_nominal').hide();
        $('#nominal').val(titipan);
    }
});
</script>


<script>
    $(document).ready( function () {
    $('.tabel-member-awal').DataTable();
} );
</script>


<script>
function showKelompok(){
    $('#modal-kelompok').modal('show');
    $('.tabel-detail-kelompok').DataTable().destroy();

}
</script>

<script>
$('.pilih-kelompok').on('click',function(){
    const nama = $(this).data('nama');
    const id = String($(this).data('id'));

    $('#kode_kelompok').val(id);       
    $('#nama_kelompok').val(nama);

    listTransaksiKelompok(id);
    
})
</script>


<script>
function selectMember(){
$('#modal-member').modal('hide');
};

$('.pilih-member').on('click',function(){
    var  url;
    const id = $(this).data('id');
    const nama = $(this).data('nama');
    $('#id_member').val(id + " - " + nama);       
    $('#id').val(id);
    $('.input-transaksi').attr('disabled',false);
                
    url ="{{ route('angsuran.getMember',':id')}}";
    url = url.replace(':id',id);
    $.ajax({
        url : url,
        type: "GET",
        data: {id:id},
        dataType: 'json',
        success: function(data){
            // console.log(data);
            
            if(data.titipan == null){
                $('#nominal_titipan').val(0);
                $('#nominal').val(0);
            }else{
                $('#nominal_titipan').val(data.titipan);
                $('#nominal').val(data.titipan);
            }
                
            $('#nominal_angsuran').val(data.angsuran);
            $('#nominal_os').val(data.os);
        },
        error : function(){
            alert("Member Tidak ada");
        }
    }); 
});
</script>


<script>
function listTransaksiKelompok(id){
    $('.tabel-transaksi-kelompok').show(); 

    var table,url;
    url = "{{route('angsuran.listTransaksiKelompok',':id')}}";
    url = url.replace(':id',id);
    table = $('.tabel-detail-kelompok').DataTable({
        "paging":false,
        "scrollY":"300px",
        "ajax":{
            url: url,
            type: "GET"
        }        
    });
}
</script>


<script>
function listTransaksi(id){
    $('.tabel-transaksi').show(); 

    var table,url;
    url = "{{route('angsuran.listTransaksi',':id')}}";
    url = url.replace(':id',id);
    table = $('.tabel-detail').DataTable({
        "ajax":{
            url: url,
            type: "GET"
        }        
    });
}
</script>


<script>
function selectKelompok(data){
$('#modal-kelompok').modal('hide');   
};
</script>

<script>
function addTransaksi(){
    
    var keterangan = $('#keterangan_transaksi').children("option:selected").val();
    var id = $('#id').val();
    var id_member = $('#id_member').val();
    var nominal = $('#nominal').val();
    var titipan = $('#nominal_titipan').val()
    var angsuran = $('#nominal_angsuran').val();
    var os = $('#nominal_os').val();
    var jenis =  $('#keterangan_transaksi').children("option:selected").text();
    
    if (keterangan == 0) {
        alert('Pilih Keterangan');
    }else if (id == 0 || id == null){
        alert('Masukan id member');
    }else if (keterangan == "kurang" && nominal == 0){
        alert('Masukan nominal');
    }else if (keterangan == "pelunasan" && nominal == 0){
        alert('Masukan nominal');
    }else{
        transaksi(id);
    }
    
}
</script>

<script>
function transaksi(id){
    $.ajax({
            url:"{{ route('angsuran.addTransaksi')}}",
            type: "POST",
            data: $('.form-individu').serialize(),
            success: function(data){
                if (data.alert) {
                    alert(data.alert)
                }else{
                    alert('data berhasil ditambahkan');
                    listTransaksi(id);
                    $('#id_member').val("");       
                    $('#id').val("");
                    $('#nominal').val("");                
                    $('.input-transaksi').attr('disabled',true);
                    $('#jenis_transaksi').attr('disabled',true);
                    $('.show-produk').attr('disabled',true);
                }
            },
            error : function(){
                alert("Tidak dapa menyimpad data!");
            }
        }); 


};
</script>

<script>
function showMember(){
    $('#modal-member').modal('show');
    $('.tabel-transaksi').hide();
    $('.tabel-detail').DataTable().destroy();
    $('.input-transaksi').attr('disabled',true);
}
</script>

<script>
$('.selesai').on('click',function(){
    location.reload(true);
})

</script>

<script>

$('#select-all').click(function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;
            $(".proses").attr("disabled",false);                   
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;
            $(".proses").attr("disabled",true);                       
        });
    }
});
</script>

<script>

var n,o;

function check(){
    n = $(".id_member_check:checked").length;
    o = $(".id_member_check").length

    if (n == 0) {
        $(".proses").attr("disabled",false);
    }else{
        $(".proses").attr("disabled",true);
    }
}



</script>
@endsection


