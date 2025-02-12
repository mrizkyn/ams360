@extends('adminlte::page')

@section('title', 'Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Perusahaan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="/assessment/companies/{{ $company->id }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="namaPerusahaan">Nama Perusahaan</label>
                            <input type="text" class="form-control @error('namaPerusahaan') is-invalid @enderror"
                                placeholder="Masukkan nama perusahaan" name="namaPerusahaan" id="namaPerusahaan"
                                value="{{ $company->name }}">
                            @error('namaPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        {{-- <div class="form-group">
                            <label for="bidangIndustri">Bidang Usaha</label>
                            <select name="bidangIndustri" id="bidangIndustri" class="form-control @error('bidangIndustri') is-invalid @enderror">
                                <option value="0" disabled>== Pilih Bidang Usaha ==</option>
                                @foreach ($business as $data)
                                <option value="{{$data->id}}" @if ($company->business_field_id == $data->id) selected @endif>{{ $data->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="form-group">
                            <label for="alamatPerusahaan">Alamat Perusahaan</label>
                            <textarea name="alamatPerusahaan" id="alamatPerusahaan" placeholder="Masukkan alamat perusahaan"
                                class="form-control @error('alamatPerusahaan') is-invalid @enderror">{{ $company->address }}</textarea>
                            @error('alamatPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="kotaPerusahaan">Kota Perusahaan</label>
                            <input type="text" class="form-control @error('kotaPerusahaan') is-invalid @enderror"
                                placeholder="Masukkan kota perusahaan" name="kotaPerusahaan" id="kotaPerusahaan"
                                value="{{ $company->city }}">
                            @error('kotaPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="noTelpPerusahaan">No. Telp Perusahaan</label>
                            <input type="text" class="form-control @error('noTelpPerusahaan') is-invalid @enderror"
                                placeholder="Masukkan no. telp perusahaan" name="noTelpPerusahaan" id="noTelpPerusahaan"
                                value="{{ $company->phone }}">
                            @error('noTelpPerusahaan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="namaPIC">Nama PIC</label>
                            <input type="text" class="form-control @error('namaPIC') is-invalid @enderror"
                                placeholder="Masukkan nama PIC" name="namaPIC" id="namaPIC"
                                value="{{ $company->pic_name }}">
                            @error('namaPIC')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="noTelpPIC">No. Telp PIC</label>
                            <input type="text" class="form-control @error('noTelpPIC') is-invalid @enderror"
                                placeholder="Masukkan no. telp PIC" name="noTelpPIC" id="noTelpPIC"
                                value="{{ $company->pic_phone }}">
                            @error('noTelpPIC')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="emailPIC">Email PIC</label>
                            <input type="email" class="form-control @error('emailPIC') is-invalid @enderror"
                                placeholder="Masukkan email PIC" name="emailPIC" id="emailPIC"
                                value="{{ $company->pic_mail }}">
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
                            <input type="submit" name="submit" value="Ubah" class="btn btn-info">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
