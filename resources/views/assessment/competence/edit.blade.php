@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Kompetensi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/competencies/{{ $id }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label for="name">Nama Kompetensi</label>
                            @error('name')
                                <div class="alert alert-danger" style="margin-top: 10px">{{ $message }}</div>
                            @enderror
                            <input type="type" name="name" class="form-control @error('name') is-invalid @enderror"
                                id="name" value="{{ $name }}">
                        </div>
                        <div class="form-group">
                            <label for="definition">Definisi Kompetensi</label>
                            <textarea class="form-control @error('definition') is-invalid @enderror" name="definition" id="definition"
                                rows="5">{{ $definition }}</textarea>
                            @error('definition')
                                <div class="alert alert-danger" style="margin-top: 10px">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="action" style="text-align: right">
                            <a href="/assessment/competencies" class="btn btn-info">Kembali</a>
                            <button type="submit" class="btn btn-info">Ubah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
