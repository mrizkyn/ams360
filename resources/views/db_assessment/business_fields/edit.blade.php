@extends('adminlte::page')

@section('title', 'Bidang Usaha')

@section('content_header')
<h1 class="m-0 text-dark">Bidang Usaha</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <form action="/db-assessment/business-fields/{{ $business_fields->id }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Bidang Usaha</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            placeholder="Nama Perusahaan" name="name" value="{{ old('name', $business_fields->name) }}" required>
                        @error('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="text-align: right">
                        <a href="/db-assessment/business-fields" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Ubah</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop