<!DOCTYPE html>
<html>
<head>
   <title>Nota PDF</title>
   <style type="text/css">
      table td{font-family: "Courier New", Courier, monospace ;
         font-size:29px;
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
     {{ $toko->alamat_pendek }}<br><br>
     </td>
     <td>Tanggal</td>
     <td>: {{ tanggal_indonesia(date('Y-m-d')) }}</td>
  </tr>     
  <tr>
     <td>Kode Member</td>
     <td>: {{ $penjualan->kode_member }}</td>
  </tr>

<tr>
     <td>Kode Struk</td>
     <td>: {{ $penjualan->id_penjualan }}</td>
  </tr>
</table>
<table width="100%" class="data">
<thead>
   <tr>
    <th>No</th>
    <th>Kode </th>
    <th>Nama </th>
    <th>Harga </th>
    <th>Jumlah</th>
    <th>Diskon</th>
    <th>Subtotal</th>
   </tr>
   </thead>
   </table>        
<table width="100%" class="data">
   <tbody>
    @foreach($detail as $data)
      
    <tr>
       <td>{{ ++$no }}</td>
       <td>{{ substr($data->kode_produk,8) }}</td>
       <td>{{ $data->nama_produk }}</td>
       <td align="right">{{ format_uang($data->harga_jualnya) }}</td>
       <td>x{{ $data->jumlah }}</td>
       <td align="right">{{ format_uang($data->diskon) }}</td>
       <td align="right">{{ format_uang($data->sub_total) }}</td>
    </tr>
    @endforeach
   
   </tbody>
   <tfoot>
    <tr><td colspan="6" align="right">Total Harga</td><td align="right">{{ format_uang($penjualan->total_harga) }}<</td></tr>
    <tr><td colspan="6" align="right">Diskon</td><td align="right">{{ format_uang($penjualan->diskon) }}</td></tr>
    <tr><td colspan="6" align="right">Total Bayar</td><td align="right">{{ format_uang($penjualan->bayar) }}</td></tr>
    <tr><td colspan="6" align="right">Diterima</td><td align="right">{{ format_uang($penjualan->diterima) }}</td></tr>
    <tr><td colspan="6" align="right">Kembali</td><td align="right">{{ format_uang($penjualan->diterima - $penjualan->bayar) }}</td></tr>
   </tfoot>
</table>

<table width="100%">
  <tr>
    <td>
      Terimakasih telah berbelanja dan sampai jumpa
    </td>
    <td align="center">
      Kasir<br><br><br> {{Auth::user()->name}}
    </td>
  </tr>
</table>
</body>
</html>
