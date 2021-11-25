<!DOCTYPE html>
<html lang="en">
<head>
    {{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> --}}
    <style>
    body{
        
    }
    .box1{
        width:320px;
        height:95px;
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
        height:100px;
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
        height: 170px;
        margin-left: 84px;
    }

    .box4{
        width:650px;
        height:100px;
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
        <strong>Penerima Barang :</strong><br>
        
        <table cellspacing="0">
        <tr>
        <td>Nama Toko</td>
        <td>: {{$alamat_penerima->nama_toko}}</td>
        </tr>
        <tr>
        <td>Kode Gudang</td>
        <td>: {{$alamat_penerima->kode_toko}}</td>
        </tr>
        <tr>
        <td>Alamat</td>
        <td>: {{$alamat_penerima->alamat}} - {{$alamat_penerima->region}}</td>
        </tr>
        </table>
    </div>

<!-- /ship -->

<!-- /surat jaln -->
<div class="box-kiri">
    <div class="box3"><strong>Stok Transfer</strong></div>

    <div class="box2">
        <table>
                
            <tr>
                <td>No Purchase Order</td>
            <td>: {{$nomer_surat}}</td>
            </tr>
            @foreach ($data_surat as $d)
            <tr>
                <td>Date</td>
                <td>: {{tanggal_indonesia(substr($d->created_at, 0, 10), false)}}</td>
            </tr>
            @endforeach
            <tr>
                <td>Pengirim</td>
                
                <td>: {{$alamat_pengirim->nama_gudang}} ({{$alamat_pengirim->kode_gudang}})</td>
               
            </tr>
        </table>
    </div>
</div>

<div class="box4">
    <table border="1" cellpadding="1" cellspacing="0" width="706px">
        <thead>
        <tr>
            <th width="30px" style="text-align:center;">No.</th>
            <th width="90px" style="text-align:center;">Barcode</th>
            <th style="text-align:center;">Product Name</th>
            <th width="70px" style="text-align:center;">Qty</th>
            <th width="100px" style="text-align:center;">Expired Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produk as $d)

            <tr>
                <td style="text-align:center;" width="30px" >{{$no++}}</td>
                <td style="text-align:center;" width="90px" >{{$d->kode_produk}}</td>
                <td style="text-align:left;" width="240px">{{$d->nama_produk}}</td>
                <td style="text-align:center;" width="70px" >{{$d->jumlah}}</td>
                <td style="text-align:center;" width="70px" >{{$d->expired_date}}</td>
                    </tr>
            @endforeach
            

            <tr>
                <td style="text-align:center;" width="30px" ></td>
                <td style="text-align:center;" width="90px" ></td>
                <td style="text-align:right;" width="240px"><i><b>TOTAL </i></b></td>
                <td style="text-align:center;" width="70px" >{{$alamat['total_item']}}</td>
                <td style="text-align:center;" width="70px" ></td>
            </tr>
            
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
