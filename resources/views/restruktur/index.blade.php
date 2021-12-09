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
                <form autocomplete="off" class="form-wo" action="{{route('write_off.store')}}" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="cari">ID Member</label>
                    <input id="produk" type="text" class="form-control"  onClick="this.value = ''" name="produk" autofocus required>
                    <div id="parentProdukList"></div>
                </div>
                <input type="hidden" name="kode" id="kode">

                <div class="form-group">
                    <label for="jenis">Jenis Restrukturisasi</label>
                    <select name="jenis" class="form-control">
                        <option>1</option>
                        <option>2</option>
                      </select>
                </div>

                <div class="form-group">
                    <label for="tenor">Tenor</label>
                    <input type="number" class="form-control" name="tenor" id="tenor" placeholder="Tenor" required>
                </div>

                <button type="button" class="btn btn-primary pull-right proses">Cari</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
