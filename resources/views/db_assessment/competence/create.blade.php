@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Kompetesi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/db-assessment/competencies" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Kompetensi</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Nama Kompetensi" name="name" value="{{old('name')}}">
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div style="text-align: right">
                            <a href="/db-assessment/competencies" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
