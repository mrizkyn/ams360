@extends('adminlte::page')

@section('title', 'Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Perusahaan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="/assessment/companies" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="namaPerusahaan">Nama Perusahaan</label>
                            <input type="text" class="form-control @error('namaPerusahaan') is-invalid @enderror" placeholder="Masukkan nama perusahaan" name="namaPerusahaan" id="namaPerusahaan" value="{{ old('namaPerusahaan') }}">
                            @error('namaPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="bidangIndustri">Bidang Usaha</label>
                            <select name="bidangIndustri" id="bidangIndustri" class="form-control @error('bidangIndustri') is-invalid @enderror">
                                <option value="0" disabled selected>== Pilih Bidang Usaha ==</option>
                                @foreach ($business as $item)
                                    <option value="{{ $item->id }}" @if (old('bidangIndustri') == $item->id) selected @endif>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('bidangIndustri')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="alamatPerusahaan">Alamat Perusahaan</label>
                            <textarea name="alamatPerusahaan" id="alamatPerusahaan" placeholder="Masukkan alamat perusahaan" class="form-control @error('alamatPerusahaan') is-invalid @enderror">{{ old('alamatPerusahaan') }}</textarea>
                            @error('alamatPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="kotaPerusahaan">Kota Perusahaan</label>
                            <input type="text" class="form-control @error('kotaPerusahaan') is-invalid @enderror" placeholder="Masukkan kota perusahaan" name="kotaPerusahaan" id="kotaPerusahaan" value="{{ old('kotaPerusahaan') }}">
                            @error('kotaPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="noTelpPerusahaan">No. Telp Perusahaan</label>
                            <input type="text" class="form-control @error('noTelpPerusahaan') is-invalid @enderror" placeholder="Masukkan no. telp perusahaan" name="noTelpPerusahaan" id="noTelpPerusahaan" value="{{ old('noTelpPerusahaan') }}">
                            @error('noTelpPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="namaPIC">Nama PIC</label>
                            <input type="text" class="form-control @error('namaPIC') is-invalid @enderror" placeholder="Masukkan nama PIC" name="namaPIC" id="namaPIC" value="{{ old('namaPIC') }}">
                            @error('namaPIC')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="noTelpPIC">No. Telp PIC</label>
                            <input type="text" class="form-control @error('noTelpPIC') is-invalid @enderror" placeholder="Masukkan no. telp PIC" name="noTelpPIC" id="noTelpPIC" value="{{ old('noTelpPIC') }}">
                            @error('noTelpPIC')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="emailPIC">Email PIC</label>
                            <input type="email" class="form-control @error('emailPIC') is-invalid @enderror" placeholder="Masukkan email PIC" name="emailPIC" id="emailPIC" value="{{ old('emailPIC') }}">
                            @error('emailPIC')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form-group text-right">
                            <a href="/assessment/companies" class="btn btn-default">Kembali</a>
                            <input type="submit" name="submit" value="Simpan" class="btn btn-info">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
