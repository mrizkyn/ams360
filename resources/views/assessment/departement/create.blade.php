@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Departemen</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/departements" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Departemen</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                placeholder="Nama Departemen" name="name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div style="text-align: right">
                            <a href="/assessment/departements" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
