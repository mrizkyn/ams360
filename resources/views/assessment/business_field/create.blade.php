@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 text-dark">Tambah Bidang Usaha</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="/assessment/business-fields" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Bidang Usaha</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            placeholder="Nama Bidang Usaha" name="name" value="{{old('name')}}">
                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div style="text-align: right">
                        <a href="/assessment/business-fields" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
