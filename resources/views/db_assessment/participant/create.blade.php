@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Asesi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="/db-assessment/participants" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <p class="card-title"> Data Pribadi </p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Asesi</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                placeholder="Nama Asesi" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="city">Tempat Lahir</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                                placeholder="Tempat Lahir" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="birth">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('birth') is-invalid @enderror" id="birth"
                                name="birth" value="{{ old('birth') }}">
                            @error('birth')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">No Telephone/Hp</label>
                            <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                placeholder="No Telephone/Hp" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                placeholder="Alamat Email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <p class="card-title">Informasi Asesi Di Perusahaan </p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="company_id">Nama Perusahaan</label>
                            <select class="form-control @error('company_id') is-invalid @enderror" name="company_id"
                                id="company_id">
                                <option value="0" disabled selected>== Pilih Perusahaan ==</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="identity_number">NIK</label>
                            <input type="text" class="form-control @error('identity_number') is-invalid @enderror"
                                id="identity_number" placeholder="No NIK" name="identity_number"
                                value="{{ old('identity_number') }}">
                            @error('identity_number')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="division">Divisi</label>
                            <select class="form-control @error('division') is-invalid @enderror" name="division"
                                id="division">
                                <option value="0" disabled selected>== Pilih Divisi ==</option>
                                @foreach ($divisions as $value)
                                    <option
                                        value="{{ $value->id }} {{ old('division') == $value->id ? 'selected' : '' }}">
                                        {{ $value->name }}</option>
                                @endforeach
                            </select>
                            @error('division')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="departement">Departemen</label>
                            <select class="form-control @error('departement') is-invalid @enderror" name="departement"
                                id="departement">
                                <option value="0" disabled selected>== Pilih Departemen ==</option>
                                @foreach ($departements as $value)
                                    <option value="{{ $value->id }}"
                                        {{ old('departement') == $value->id ? 'selected' : '' }}>{{ $value->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="position">Jabatan</label>
                            <select class="form-control @error('position') is-invalid @enderror" name="position"
                                id="position">
                                <option value="0" disabled selected>== Pilih Jabatan ==</option>
                                @foreach ($positions as $value)
                                    <option value="{{ $value->id }}"
                                        {{ old('position') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                            @error('position')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div style="text-align: right">
                            <a href="/db-assessment/competencies" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
