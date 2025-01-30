@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 text-dark">Tambah Kompetensi</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="/assessment/competencies" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Kompetensi</label>
                        <input type="type" name="name" class="form-control @error('name') is-invalid @enderror"
                            id="name" placeholder="Masukan Nama Kompetensi" value="{{ old('name') }}">
                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="definition">Definisi Kompetensi</label>
                        <textarea class="form-control @error('definition') is-invalid @enderror" name="definition"
                            id="definition" rows="5">{{ old('definition') }}</textarea>
                        @error('definition')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="action" style="text-align: right">
                        <a href="/assessment/competencies" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
