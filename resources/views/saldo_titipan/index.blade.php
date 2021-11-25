@extends('layouts.app')

@section('title')
  Saldo Titipan
@endsection

@section('breadcrumb')
   @parent
   <li>saldo_titipan</li>
@endsection

@section('header')


<style>

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type=number] {
    -moz-appearance:textfield; /* Firefox */
}

</style>

@endsection

@section('content')     
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-body">
                
            <form class="form form-horizontal form-produk">
                <div class="form-group">
                    <label class="col-md-2 control-label" for="kode">Kode Member</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" class="form-control" id="kode" name="kode" autofocus required>
                            <span class="input-group-btn">
                                <button id="member-show" onclick="showMember()" type="button" class="btn btn-info">...</button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="kode_produk">ID Member</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="id-member" name="id_member" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="nama">Nama Member</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" id="nama" name="nama" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="stok">OS</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control no-border" id="os" name="os" readonly>
                        </div>
                    </div>
                </div>
                
                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="stok">Angsuran</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control no-border" id="angsuran" name="angsuran" readonly>
                        </div>
                    </div>
                </div>

                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="stok">Titipan</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control no-border" id="titipan" name="titipan" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label" for="stok">Tunggakan</label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control no-border" id="tunggakan" name="tunggakan" readonly>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
        <div class="box-header">
        
        </div>
        
        <div class="box-body box-detail">
       <table class="table table-striped tabel-detail" >
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>

                <tbody>

                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

@include('saldo_titipan.member')
@endsection

@section('script')

<script>
    $(document).ready( function () {
    $('.tabel-member-awal').DataTable();
} );
</script>


<script>

function showMember(){
    $('#modal-member').modal('show');
    $('.tabel-detail').DataTable().destroy();
    $('#id-member').val('');
    $('#nama').val('');
    $('#angsuran').val('');
    $('#kode').val('');
    $('#plafond').val('');
    $('#tunggakan').val('');
    $('#os').val('');
    $('#titipan').val('');
}
</script>

<script type="text/javascript">
// listdata detail produk
var table,url_data,url_titipan,url_detail;
// ketika produk dipilih
function selectMember(kode){
    $('#modal-member').modal('hide');
}
// nampilin data 

// pilih item
$('.pilih-member').on('click',function(){
    const id = $(this).data('id');
    url_data = "{{route('saldo_titipan.getData',':id')}}";
    url_data = url_data.replace(':id',id);
    $.ajax({
        url: url_data,
        data:{id : id},
        method: 'get',
        dataType: 'json',
        success: function(data){
            $('#kode').val(data.id_member);
            $('#nama').val(data.Cust_Short_name);
            $('#id-member').val(data.id_member);
            $('#tunggakan').val(data.tunggakan);
            $('#os').val(data.os);
            $('#angsuran').val(data.angsuran);
        },
        error: function(){
            alert("Member Tidak Ada");
        }
    });
    
    url_titipan = "{{route('saldo_titipan.getTitipan',':id')}}";
    url_titipan = url_titipan.replace(':id',id);
    $.ajax({
        url: url_titipan,
        data:{id : id},
        method: 'get',
        dataType: 'json',
        success: function(data){
            $('#titipan').val(data.titipan);
        },
        error: function(){
            alert("Member Tidak Ada");
        }
    });
    

    $(function(){
    url_detail = "{{route('saldo_titipan.listDetail',':id')}}";
    url_detail = url_detail.replace(':id',id);

        table = $('.tabel-detail').DataTable({
            "dom" : 'Brt',
            "bSort" : false,
            "processing" : true,
            "servrSide" : true,
            "paging": false,
            "ajax" : {
            "url" : url_detail,
            "type" : "GET"
            }
        })    
    });
})


$('#kode').on('keypress',function(e){
    if(e.which == 13) {
        const id = $('#kode').val();
        // table.destroy();
        url_data = "{{route('saldo_titipan.getData',':id')}}";
        url_data = url_data.replace(':id',id);
        $.ajax({
            url: url_data,
            data:{id : id},
            method: 'get',
            dataType: 'json',
            success: function(data){
                $('#kode').val(data.id_member);
                $('#nama').val(data.Cust_Short_name);
                $('#id-member').val(data.id_member);
                $('#tunggakan').val(data.tunggakan);
                $('#os').val(data.os);
                $('#angsuran').val(data.angsuran);
            },
            error: function(){
                alert("Member Tidak Ada");
            }
        });
        
        url_titipan = "{{route('saldo_titipan.getTitipan',':id')}}";
        url_titipan = url_titipan.replace(':id',id);
        $.ajax({
            url: url_titipan,
            data:{id : id},
            method: 'get',
            dataType: 'json',
            success: function(data){
                $('#titipan').val(data.titipan);
            },
            error: function(){
                alert("Member Tidak Ada");
            }
        });
        

        $(function(){
        url_detail = "{{route('saldo_titipan.listDetail',':id')}}";
        url_detail = url_detail.replace(':id',id);

            table = $('.tabel-detail').DataTable({
                "dom" : 'Brt',
                "bSort" : false,
                "processing" : true,
                "servrSide" : true,
                "paging": false,
                "ajax" : {
                "url" : url_detail,
                "type" : "GET"
                }
            })    
        });
    
    }
})

</script>





@endsection
