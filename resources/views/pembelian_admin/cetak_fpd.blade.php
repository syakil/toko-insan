<!DOCTYPE html>
<html lang="en">
<head>
    {{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> --}}
    <style>
    body{
        
    }
    .box1{
        width:320px;
        height:90px;
        padding-top: 5px;
        padding-left: 5px;
        margin-top: 40px;
        margin-left: 10px;
        font-size: 12px;
        display:inline-block;
        border:1px solid black;
    }

    .box2{
        width:250px;
        height:120px;
        padding-top: 12px;
        padding-left: 7px;
        margin-left: 30px;
        font-size: 12px;
        border:1px solid black;
        /* display: inline-block; */
    }

    .box3{
        width:250px;
        height:42px;
        padding-top: 12px;
        padding-left: 7px;
        margin-left: 30px;  
        text-align: center;
        font-size: 20px;
        border:1px solid black;
    }

    .box-kiri{
        display: inline-block;
        width: 300px;
        height: 190px;
        margin-left: 84px;
    }

    .box4{
        width:650px;
        height:120px;
        margin-top: -25px;
        margin-left: 10px;
        font-size: 12px;
        /* display: inline-block; */
    }

    </style>
    
</head>
<body>
    <!-- -- ship to outlet -- -->

    <div class="box1">
        <table cellspacing="0">
        <tr>
        <td>No.</td>
        <td>: </td>
        </tr>
        <tr>
        <td>Kepada</td>
        <td>: Finance</td>
        </tr>
        <tr>
        <td>Dari</td>
        <td>: Purchasing - {{$alamat->nama_gudang}}</td>
        </tr>
        <tr>
        <td>Bagian</td>
        <td>: Admin</td>
        </tr>
        <tr>
        <td>Tanggal</td>
        <td>: {{date('Y-m-d')}}</td>
        </tr>
        </table>
    </div>

<!-- /ship -->

<!-- /surat jaln -->
<div class="box-kiri">
    <div class="box3"><strong>Permohonan Dana</strong></div>

    <div class="box2">
        <table>
            @foreach ($nosurat as $d)
                
            <tr>
                <td>No Purchase Order</td>
            <td>: PO - {{$d->id_pembelian}}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td>: {{tanggal_indonesia(substr($d->created_at, 0, 10), false)}}</td>
            </tr>
            @endforeach
            <tr>
                <td>Pengirim</td>
                <td>: {{$alamat->nama}}</td>
            </tr>
        </table>
    </div>
</div>

<div class="box4">
    <table border="1" cellpadding="1" cellspacing="0" width="705px">
        <thead>
        <tr>
            <th width="30px" style="text-align:center;">No.</th>
            <th width="90px" style="text-align:center;">Keperluan</th>
            <th style="text-align:center;">Jumlah</th>
            <th width="70px" style="text-align:center;">Keterangan</th>
        </tr>
        </thead>
        <tbody>

            <tr>
                <td style="text-align:center;" width="30px">{{$no++}}</td>
                <td style="text-align:left;" width="100px">Pembelian Stok Barang {{$alamat->nama_gudang}}<br>
@if($alamat->tipe_bayar == 1)
TOP tanggal {{$alamat->jatuh_tempo}}
@endif
</td>
                <td style="text-align:center;" width="70px">Rp. {{format_uang($produk->total_harga_terima)}}</td>
                <td style="text-align:left;" width="70px" >Rekening Bank {{$alamat->bank}} : <br> {{$alamat->no_rek}} <br> a/n {{$alamat->nama_rek}}</td>
            </tr>
        </tbody>
    </table>
<br><br><br>
    <div style="font-size:12px; margin-left:45px; display:inline-block;">
        Dibuat Oleh,<br><br><br><br>
        _____________
    </div>

    <div style="font-size:12px; margin-left:80px;display:inline-block;">
        Mengetahui,<br><br><br><br>
        _____________
    </div>
    <div style="font-size:12px; margin-left:80px;display:inline-block;">
    </div>
<div style="font-size:12px; margin-left:80px;display:inline-block;">
    </div>
        
<div style="font-size:12px; margin-left:80px;display:inline-block;">
        Disetujui Oleh,<br><br><br><br>
        _____________
    </div>
</div>

</body>
</html>
