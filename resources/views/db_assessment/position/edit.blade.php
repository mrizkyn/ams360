@extends('adminlte::page')

@section('title', 'Jabatan')

@section('content_header')
    <h1 class="m-0 text-dark">Jabatan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="/db-assessment/positions/{{ $position->id }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Jabatan</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" value="{{old('name', $position->name) }}" name="name" id="name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form-group text-right">
                            <a href="/db-assessment/positions" class="btn btn-default">Kembali</a>
                            <input type="submit" name="submit" value="Ubah" class="btn btn-info">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
