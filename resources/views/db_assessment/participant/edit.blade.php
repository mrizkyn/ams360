@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Asesi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="/db-assessment/participants/{{$participant->id}}" method="POST">
                @method('put')
                @csrf
                <div class="card">
                    <div class="card-header">
                        <p class="card-title"> Data Pribadi </p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Asesi</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Nama Kompetensi" name="name" value="{{$participant->name}}">
                            @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="city">Tempat Lahir</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" placeholder="Tempat Lahir" name="city" value="{{$participant->city}}">
                            @error('city')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="birth">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('birth') is-invalid @enderror" id="birth" name="birth" value="{{$participant->birth}}">
                            @error('birth')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">No Telephone/Hp</label>
                            <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone" placeholder="No Telephone/Hp" name="phone" value="{{$participant->phone}}">
                            @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Alamat Email" name="email" value="{{$participant->email}}">
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <p class="card-title">Informasi Asesi Di Perusahaan</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="company_id">Nama Perusahaan</label>
                            <select class="form-control @error('company_id') is-invalid @enderror" name="company_id" id="company_id">
                                <option value="0" disabled selected>== Pilih Perusahaan ==</option>
                                @foreach ($companies as $company)
                                    <option value="{{$company->id}}" @if($company->id == $participant->company_id) selected @endif> {{$company->name}}</option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="identity_number">NIK</label>
                            <input type="text" class="form-control @error('identity_number') is-invalid @enderror" id="identity_number" placeholder="No NIK" name="identity_number" value="{{$participant->identity_number}}">
                            @error('identity_number')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="company_id">Divisi</label>
                            <select class="form-control @error('division') is-invalid @enderror" name="division" id="division">
                                <option value="0" disabled selected>== Pilih Divisi ==</option>
                                @foreach ($divisions as $division)
                                    <option value="{{$division->id}}" @if($division->id == $participant->division_id) selected @endif> {{$division->name}}</option>
                                @endforeach
                            </select>
                            @error('division')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="company_id">Departemen</label>
                            <select class="form-control @error('departement') is-invalid @enderror" name="departement" id="departement">
                                <option value="0" disabled selected>== Pilih Departemen ==</option>
                                @foreach ($departements as $departement)
                                    <option value="{{$departement->id}}" @if($departement->id == $participant->departement_id) selected @endif> {{$departement->name}}</option>
                                @endforeach
                            </select>
                            @error('departement')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="company_id">Jabatan</label>
                            <select class="form-control @error('position') is-invalid @enderror" name="position" id="position">
                                <option value="0" disabled selected>== Pilih Jabatan ==</option>
                                @foreach ($positions as $position)
                                    <option value="{{$position->id}}" @if($position->id == $participant->position_id) selected @endif> {{$position->name}}</option>
                                @endforeach
                            </select>
                            @error('position')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div style="text-align: right">
                            <a href="/db-assessment/participants" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-primary">Ubah</button>
                        </div>
                    </div>
                </div>                   
            </form>
        </div>
    </div>
@stop