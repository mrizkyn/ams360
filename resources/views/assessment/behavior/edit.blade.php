@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Perilaku Kunci</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="/assessment/behaviors/{{ $id }}" method="POST">
                  @csrf
                  @method('PATCH')
                  <div class="form-group">
                    <label for="name">Nama Kompetensi</label>
                    <input type="type" name="name" readonly class="form-control" id="name" value="{{ $name }}" >
                  </div>
                  <div class="form-group">
                    <label for="definition">Definisi Kompetensi</label>
                    <textarea class="form-control" readonly name="definition" id="definition" rows="3">{{ $definition }}</textarea>
                  </div>
                  <div class="form-group">
                    <label for="description">Perilaku Kunci</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"  name="description" id="description" rows="3">{{ $description }}</textarea>
                    @error('description')
                        <div class="alert alert-danger" style="margin-top: 10px">{{$message}}</div>
                    @enderror
                  </div>
                  <div class="action" style="text-align: right">
                    <a href="/assessment/competencies" class="btn btn-default">Kembali</a>
                    <button type="submit" class="btn btn-primary">Ubah</button>
                  </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
