@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Saran Pengembangan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/developments-source" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="competence_id">Nama Kompetensi</label>
                            <select class="form-control @error('competence_id') is-invalid @enderror" name="competence_id"
                                id="competence_id">
                                <option value="" selected disabled>Pilih Kompetensi</option>
                                @foreach ($competencies as $competency)
                                    <option value="{{ $competency->id }}"
                                        {{ old('competence_id') == $competency->id ? 'selected' : '' }}>
                                        {{ $competency->name }}</option>
                                @endforeach
                            </select>
                            @error('competence_id')
                                <div class="alert alert-danger" role="alert"> {{ $message }} </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="description">Saran Pengembangan</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                                rows="3"></textarea>
                            @error('description')
                                <div class="alert alert-danger" role="alert"> {{ $message }} </div>
                            @enderror
                        </div>
                        <div style="text-align: right">
                            <a href="/assessment/developments-source" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
