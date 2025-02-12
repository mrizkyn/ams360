@extends('adminlte::page')

@section('title', 'Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Perusahaan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="/db-assessment/companies" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Perusahaan</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                placeholder="Nama Perusahaan" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="business_field">Bidang Usaha</label>
                            <select class="form-control @error('business_field') is-invalid @enderror" name="business_field"
                                id="business_field" required>
                                <option value="0" disabled selected>== Pilih Bidang Usaha ==</option>
                                @foreach ($businessFields as $businessField)
                                    <option value="{{ $businessField->name }}">{{ $businessField->name }}</option>
                                @endforeach
                            </select>
                            @error('business_field')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" placeholder="Alamat" name="address"
                                rows="2" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="city">Kota</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                                placeholder="Nama Kota" name="city" value="{{ old('city') }}" required>
                            @error('city')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">No Telepon Perusahaan</label>
                            <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                placeholder="No Telephone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <p class="card-title"> Informasi Person In Charge </p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="pic_name">Nama PIC</label>
                            <input type="text" class="form-control @error('pic_name') is-invalid @enderror"
                                id="pic_name" placeholder="Nama PIC" name="pic_name" {{ old('pic_name') }}>
                            @error('pic_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pic_phone">No Telp/Hp PIC</label>
                            <input type="number" class="form-control @error('pic_phone') is-invalid @enderror"
                                min="0" id="pic_phone" placeholder="No Telp/Hp PIC" name="pic_phone"
                                value="{{ old('pic_phone') }}">
                            @error('pic_phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pic_mail">Email PIC</label>
                            <input type="email" class="form-control @error('pic_mail') is-invalid @enderror"
                                id="pic_mail" placeholder="Email PIC" name="pic_mail" value="{{ old('pic_mail') }}">
                            @error('pic_mail')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div style="text-align: right">
                            <a href="/db-assessment/companies" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
