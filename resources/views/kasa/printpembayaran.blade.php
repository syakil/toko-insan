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
     <td>EOD Tanggal</td>
     <td>: {{ tanggal_indonesia(date('Y-m-d')) }}</td>
  </tr>     
 
</table>
<table width="100%" class="data">
<thead>
   <tr>
    <th>No</th>
    <th>tanggal</th>
    <th>sal_awal</th>
    <th>Penjualan Cash</th>
    <th>Penjualan Musawamah</th>
    <th>Pengeluaran</th>
    <th>Saldo_akhir</th>
   
   </tr>
   </thead>
   </table>        
<table width="100%" class="data">
   <tbody>
    
      
    <tr>
       <td>{{ ++$no }}</td>
       <td>{{ $saldo_toko->tanggal }}</td>
       <td>{{ format_uang($saldo_toko->saldo_awal)}}</td>
       <td align="right">{{ format_uang($cash) }}</td>

       <td align="right">{{ format_uang($musawamah) }}</td>
              <td align="right">{{ format_uang($saldo_toko->pengeluaran) }}</td>
<td align="right">{{ format_uang($saldo_toko->saldo_akhir) }}</td>
      
    </tr>
    
   
   </tbody>
   
</table>

<table width="100%">
  <tr>
    <td>
      Terimakasih EOD Selesai
    </td>
    <td align="center">
      Kasir<br><br><br> {{Auth::user()->name}}
    </td>
  </tr>
</table>
</body>
</html>
