@extends('layouts.app')

@section('title')
  Pilih Harga
@endsection

@section('breadcrumb')
   @parent
   <li>Pilih Harga</li>
@endsection

@section('content') 

<?php $member_n = 1;

?>
<div class="table-responsive"> 
        <table class="table" table-condensed>
                <tr>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br> Member   Insan<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a href="{{ route('transaksi.member',$member_n) }}"><i class="fa fa-calculator" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(16, 148, 53); border: 3px solid rgb(255, 255, 255);"></i></a>
                    </td>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px ;" class="text-center">
                        Harga <br> Member Pabrik<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a href="pembagian_database.php"><i class="fa fa-bars" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(35, 92, 159);border: 3px solid rgb(255, 255, 255)"></i></a>
                    </td>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br> Cash Insan<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a href="sales_feedback.php"><i class="fa fa-child" style="color: rgb(255, 255, 255); font-size: 40px; box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(16, 148, 53); border: 3px solid rgb(255, 255, 255); "></i></a>
                    </td>
                    <td style="word-wrap: break-word;min-width: 50px;max-width: 100px;" class="text-center">
                        Harga <br>Cash Umum<br>
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><a href="pendaftaran.php"><i class="fa fa-group" style="color: rgb(255, 255, 255); font-size: 40px; text-shadow: 0px 0px 4px rgb(0, 0, 0); box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; background-color: rgb(241, 41, 139); border: 3px solid rgb(255, 255, 255)"></i></a>
                    </td>
                </tr> 
                

                 
        </table>
@endsection

