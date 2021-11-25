@extends('layouts.app')

@section('title')
  Pilih Harga
@endsection

@section('breadcrumb')
   @parent
   <li>Pilih Harga</li>
@endsection

@section('content') 

@if ($message = Session::get('status'))
      <div class="alert alert-danger alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button> 
          <strong>{{ $message }}</strong>
      </div>
    @endif


<div class="table-responsive"> 
        <table class="table" table-condensed>
                <tr>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br> Member Insan<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a onclick="addFormInsan()"><i class="fa fa-calculator" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(16, 148, 53); border: 3px solid rgb(255, 255, 255);"></i></a>
                    </td>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px ;" class="text-center">
                        Harga <br> Member Pabrik<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a onclick="addFormPabrik()"><i class="fa fa-bars" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(35, 92, 159);border: 3px solid rgb(255, 255, 255)"></i></a>
                    </td>
                    
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br> Cash Insan<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a id='cash-insan' href="{{route('cashinsan.new')}}"><i class="fa fa-child" style="color: rgb(255, 255, 255); font-size: 40px; box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(16, 148, 53); border: 3px solid rgb(255, 255, 255); "></i></a>
                    </td>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br>Cash Pabrik<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a  id='cash'="{{route('transaksi.new')}}"><i class="fa fa-group" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(241, 41, 139); border: 3px solid rgb(255, 255, 255)"></i></a>
                    </td>
                </tr> 
                

                 
        </table>
        @include('penjualan_detail_member_insan.member')
        @include('penjualan_detail_member_insan.pin')
        @include('penjualan_detail_member_insan.pin_baru')
        @include('penjualan_detail_member_pabrik.member')

@endsection

@section('script')
<script>
$(".allownumericwithoutdecimal").on("keypress keyup blur",function (event) {    
    $(this).val($(this).val().replace(/[^\d].+/, ""));
    if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }
});


</script>
<script type="text/javascript">

$(document).keyup(function(event) {
  if(event.keyCode == 112){ 
    $('#modal-member').modal('show'); 
  }
  if(event.keyCode == 113){ 
    $('#modal-pabrik').modal('show');
  }
  if(event.keyCode == 119){ 
    
    window.location.replace("{{route('cashinsan.new')}}")
    
  }
  if(event.keyCode == 120){ 
    window.location.replace("{{route('transaksi.new')}}")
  }
})

function addFormInsan(){
   $('#modal-member').modal('show');        
}

function addFormPabrik(){
   $('#modal-pabrik').modal('show');        
}

function addFormKoperasi(){
   $('#modal-murabahah').modal('show');        
}

</script>


   <script>
   function getMember(member){
      url = "{{route('member.check',':id')}}";
      url = url.replace(':id',member);
      $.ajax({
        url: url,
        data:{id : member},
        method: 'get',
        dataType: 'json',
        success: function(data){
            var pin = btoa(data.PIN);
            var nama = data.nama;
            $('#id_member_pin').val(member);
            $('#id_label').val(member + ' - ' + nama);
            $('#id_member_pin_baru').val(member);
            $('#id_label_baru').val(member + ' - ' + nama);
            $('#modal-member').modal('hide');
            


            if (atob(pin) == 0) {
                $('#modal-pin-baru').modal('show'); 
                $('#pin-baru-form').attr('action',"{{route('memberinsan.new_pin')}}")
            }else{
                $('#modal-pin').modal('show'); 
                $('#pin-form').attr('action',"{{route('memberinsan.new')}}")
            }
            
            $('#lupa_pin').click(function(){
                $('#modal-pin').modal('hide');
                $('#modal-pin-baru').modal('show');
            })
        },
        error: function(){
            alert("Member Tidak Ada");
        }
    });
   }  
   </script>

<script>
   function getMemberPabrik(member){
      url = "{{route('member.check',':id')}}";
      url = url.replace(':id',member);
      $.ajax({
        url: url,
        data:{id : member},
        method: 'get',
        dataType: 'json',
        success: function(data){

            var pin = btoa(data.PIN);
            var nama = data.nama;
            $('#id_member_pin').val(member);
            $('#id_label').val(member + ' - ' + nama);
            $('#id_member_pin_baru').val(member);
            $('#id_label_baru').val(member + ' - ' + nama);
            $('#modal-pabrik').modal('hide');


            if (atob(pin) == 0) {
                $('#modal-pin-baru').modal('show'); 
                $('#pin-baru-form').attr('action',"{{route('memberpabrik.new_pin')}}")
            }else{
                $('#modal-pin').modal('show'); 
                $('#pin-form').attr('action',"{{route('memberpabrik.new')}}")
            }
            
            $('#lupa_pin').click(function(){
                $('#modal-pin').modal('hide');
                $('#modal-pin-baru').modal('show');
            })
        },
        error: function(){
            alert('Member Tidak Ada');
        }
    });
   }  
   </script>

   <script>
      $(document).ready( function () {
       $('.tabel-member-awal').DataTable();
   } );
   </script>
@endsection




