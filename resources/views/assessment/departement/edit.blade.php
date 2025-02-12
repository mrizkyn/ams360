@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Departemen</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/departements/{{ $id }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Departemen</label>
                            @error('name')
                                <div class="alert alert-danger" style="margin-top: 10px">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="_method" value="PUT">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                value="{{ $departement }}" name="name">
                        </div>
                        <div style="text-align: right">
                            <a href="/assessment/departements" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Ubah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
