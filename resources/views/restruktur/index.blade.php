@extends('layouts.app')

@section('title')
Restrukturisasi
@endsection


@section('breadcrumb')
   @parent
   <li>Dashboard</li>
   <li>restruktur</li>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-6">
        <div class="box">
            <div class="box-body">                    
                <form  class="form-restruktur" action="{{route('restruktur.proses')}}" method="post">
                {{ csrf_field() }}
                    <div class="form-group">
                        <label for="cari">ID Member</label>
                        <input id="member" type="text" class="form-control"  onClick="this.value = ''" name="member" autofocus required>
                        <div id="parentMemberList"></div>
                    </div>
                    <input type="hidden" name="kode" id="kode">
                    <input type="hidden" name="tenor_data" id="tenor_data">
                    <input type="hidden" name="jenis_data" id="jenis_data">

                    <div class="form-group">
                        <label for="jenis">Jenis Restrukturisasi</label>
                        <select name="jenis" id="jenis" class="form-control">
                            <option value="0" selected disabled> - Pilih Jenis -</option>
                            <option value="pokok">Pokok</option>
                            <option value="pokokMargin">Pokok Margin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                        <input type="checkbox" id="simpanan" name="simpanan" {{ old('remember') ? 'checked' : '' }}> Simpanan
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="tenor">Tenor <small class="form-text text-muted" >(Minggu)</small></label>
                        <input type="number" class="form-control" name="tenor" id="tenor" placeholder="Tenor" required>
                    </div>

                </form>
                <button type="button" class="btn btn-primary cari"><i class="fa fa-search"></i> Cari</button>
                <button type="button" class="btn btn-danger reset" onclick="reset()" disabled><i class="fa fa-refresh"></i> Reset</button>
                
            </div>
        </div>
    </div>

    <div class="col-xs-12">
        <div class="box">
            <div class="box-body">  
                <table class="table table-striped tabel-member">
                    <thead>
                        <tr>
                            <th>Id Member</th>
                            <th>Nama</th>
                            <th>Tenor</th>
                            <th>OS</th>
                            <th>Angsuran</th>
                            <th>Saldo Margin</th>
                            <th>Ijaroh</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>    
    </div>
</div>
  

@endsection

@section('script')
<script>
    
var url = "{{route('restruktur.loadData')}}";
$('#member').keyup(function(){
  var query = $(this).val();
  if (query.length > 3) {
    $.ajax({
      url : url,
      method:"get",
      data: {query:query},
      success: function(data){
          $('#parentMemberList').append('<div id="memberList"></div>');    
          $("#memberList").fadeIn();
          $("#memberList").html(data);
      }
    })
  }else{
    $('#memberList').fadeOut();  
  }
})


$(document).on('click', 'li.member_list', function(){
  var text = $(this).text();
  var nilai = text.split(" ")
  var id_member = nilai[0];
  var nama = text.split(id_member)
  var nama_produk = text.split("-,")
  $('#memberList').remove();
  $('#member').val(text);  
  $('#kode').val(id_member)


});  

$('form').on('focus', 'input[type=number]', function (e) {
  $(this).on('wheel.disableScroll', function (e) {
    e.preventDefault()
  })
})
$('form').on('blur', 'input[type=number]', function (e) {
  $(this).off('wheel.disableScroll')
})

function dataMember(kode){
    url_member = "{{route('restruktur.listData',':kode')}}"
    url_member = url_member.replace(':kode',kode)
    table = $('.tabel-member').DataTable({
        "processing" : true,
        "serverside" : true,
        "ajax" : {
        "url" : url_member ,
        "type" : "GET"
        }
    }); 
}


$(".cari").on('click',function(){
    jenis = $('#jenis option:selected').val()
    tenor = $("#tenor").val()
    kode = $("#kode").val()

    if(tenor > 52){
        swal("Maaf !", "Tenor Lebih Dari 52 Minggu", "error"); 
    }else if(tenor <=0){    
        swal("Maaf !", "Tenor Tidak Boleh Kosong/Nol", "error"); 
    }else{
        if(kode == 0){
            swal("Maaf !", "Id Member Harus Diisi", "error");
        }else if(jenis == 0){
            swal("Maaf !", "Jenis Restruktur Harus Diisi", "error");
        }else{
            $("#member").attr('disabled',true);
            $("#jenis").attr('disabled',true);
            $("#tenor").attr('disabled',true);
            $(".reset").attr('disabled',false);
            $(".cari").attr('disabled',true);
            $("#jenis_data").val(jenis);
            $("#tenor_data").val(tenor)
            
            $('#simpanan').attr('disabled', true);
            dataMember(kode)
        }
    }
})

function reset(){
    table.clear();
    table.destroy();
    $('#jenis').prop('selectedIndex',0);
    $("#member").attr('disabled',false);
    $("#member").val("");
    $("#jenis").attr('disabled',false);
    $("#tenor").attr('disabled',false);
    $("#tenor").val("");
    $(".reset").attr('disabled',true);
    $(".cari").attr('disabled',false);
    $('#simpanan').attr('disabled', false);
    $('#simpanan').prop('checked', false);
}

function proses(){
    $.ajax({
    url : "{{ route('restruktur.proses') }}",
    type : "POST",
    data : $('.form-restruktur').serialize(),
    success : function(data){
        if (data.alert) {
            swal("Maaf !", data.alert, "error");
        }else{
            swal("Selamat !", "Member Berhasil Di Restruktur!", "success");
            reset()
        }
    },
    error : function(){
        swal("Maaf !", "Tidak Dapat Memproses Data!", "error");
    }   
  });
}

</script>
@endsection