<!DOCTYPE html>
<html>
<head>
   <title>EOD</title>
   <style type="text/css">
      table td{font-family: "Courier New", Courier, monospace ;
         font-size:27px;
      }
      
      table.data td,
      table.data th{
         border: 0px ;
         padding: 5px;
      }
      table.data th{
         text-align: center;
         table th{font-family: "Courier New", Courier, monospace ;
         font-size:27px;
      }
      table.data{ border-collapse: collapse }
   </style>
</head>
<body>

<table width="100%">
  <tr>
     <td rowspan="3" width="45%"><img src="../public/images/{{$setting->logo}}" width="150"><br>
     {{ $setting->alamat }}<br><br>
     </td>
     <td>Pembayaran Tanggal</td>
     <td>: {{ tanggal_indonesia(date('Y-m-d')) }}</td>
  </tr>     
 
</table>
<table width="100%" class="data">
<tr>
<td>Total Tunggakan :</td>
<td>{{number_format($os)}}</td>
</tr>
<tr>
<td>Jumlah Bayar :</td>
<td>{{number_format($bayar)}}</td>
</tr>
<tr>
<td>Sisa Tunggakan :</td>
<td>{{number_format($sisa)}}</td>
</tr>
</table>
<hr>

<table width="100%">
  <tr>
     <td>Tanggal</td>
     <td>: {{ tanggal_indonesia(date('Y-m-d')) }}</td>
  </tr>     
  <tr>
     <td>Kode Member</td>
     <td>: {{ $musawamah->id_member }}</td>
  </tr>
 <tr>
     <td>Nama Member</td>
     <td>: {{ $musawamah->Cust_Short_name }}</td>
  </tr>
</table>

<hr>
<table width="100%">
<tr>
    <td style="text-align:center;">
      Terimakasih
    </td>
</tr>

    <tr><td></td></tr>
    <tr><td></td></tr>

    <tr><td></td></tr>
    <tr><td></td></tr>

<tr>
    <td align="left">
      Ttd,<br><br><br>___________________
    </td>
  </tr>
</table>
</body>
</html>

