<!DOCTYPE html>
<html lang="en">
<head>
    {{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> --}}
    <style>
    body{
        
    }
    .box1{
        width:350px;
        height:110px;
        padding-top: 5px;
        padding-left: 5px;
        margin-top: 40px;
        margin-left: 30px;
        font-size: 12px;
        display:inline-block;
    }

    .box2{
        width:250px;
        height:100px;
        padding-top: 12px;
        padding-left: 7px;
        margin-left: 30px;
        font-size: 12px;
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
    }

    .box-kiri{
        display: inline-block;
        width: 300px;
        height: 170px;
        margin-left: 30px;
    }

    .box4{
        width:650px;
        height:100px;
        margin-top: -45px;
        margin-left: 30px;
        font-size: 12px;
        /* display: inline-block; */
    }

    </style>
    
</head>
<body>
    <!-- -- ship to outlet -- -->


    <div class="box1">
        <strong>Daftar Pengiriman Barang :</strong><br>
        @foreach ($alamat as $s)
        <table cellspacing="0">
        <tr>
        <td>Nama Toko</td>
        <td>: {{$s->nama_toko}}</td>
        </tr>
        <tr>
        <td>Kode Toko</td>
        <td>: {{$s->kode_toko}}</td>
        </tr>
        <tr>
        <td>Alamat</td>
        <td>: {{$s->alamat}} - {{$s->region}}</td>
        </tr>
        @endforeach
        </table>
    </div>

<!-- /ship -->

<!-- /surat jaln -->
<div class="box-kiri">
    <div class="box3"><strong>Stock Transfer To Unit</strong></div>

    <div class="box2">
        <table>
            @foreach ($nosurat as $d)
                
            <tr>
                <td>No Surat Jalan</td>
            <td>: {{$d->id_transfer}}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td>: {{$d->tanggal_transfer}}</td>
            </tr>
            @endforeach
            <tr>
                <td>Pengirim</td>
                @foreach($alamat as $d)
                <td>: {{$d->nama_gudang}} ({{$d->kode_gudang}})</td>
                @endforeach
            </tr>
        </table>
    </div>
</div>

<div class="box4">
    <table border="1" cellpadding="1" cellspacing="0" width="650px">
        <thead>
        <tr>
            <th width="40px" style="text-align:center;">No.</th>
            <th width="70px" style="text-align:center;">Barcode</th>
            <th style="text-align:center;">Product Name</th>
            <th width="70px" style="text-align:center;">Qty</th>
            <th width="100px" style="text-align:center;">Expired Date</th>
            <th width="100px" style="text-align:center;">Note</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($produk as $d)
            <tr>
                <td style="text-align:center;" >{{$no++}}</td>
                <td style="text-align:center;" >{{$d->kode_produk}}</td>
                <td style="text-align:center;" >{{$d->nama_produk}}</td>
                <td style="text-align:center;" >{{$d->total_item}}</td>
                <td style="text-align:center;" >{{$d->expired_date}}</td>
                <td style="text-align:center;" ></td>
            </tr>
            @endforeach
        </tbody>
    </table>
<br><br><br>
    <div style="font-size:12px; margin-left:45px; display:inline-block;">
        Prepared By,<br><br><br><br>
        _____________
    </div>

    <div style="font-size:12px; margin-left:80px;display:inline-block;">
        Checker,<br><br><br><br>
        _____________
    </div>
    <div style="font-size:12px; margin-left:80px;display:inline-block;">
        Sender,<br><br><br><br>
        _____________
    </div>
    <div style="font-size:12px; margin-left:80px;display:inline-block;">
        Outlet Receiver,<br><br><br><br>
        _____________
    </div>
</div>

</body>
</html>