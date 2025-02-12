@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Asesi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/participants/{{ $id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Nama Asesi</label>
                                    <input type="type" name="name"
                                        class="form-control @error('name') is-invalid @enderror" id="name"
                                        value="{{ $name }}">
                                    @error('name')
                                        <div class="alert alert-danger" style="margin-top: 10px">Nama Kompetensi tidak boleh
                                            kosong</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="name">NIK</label>
                                    <input type="type" name="identity"
                                        class="form-control @error('identity') is-invalid @enderror" id="identity"
                                        value=" {{ $nik }}">
                                    @error('identity')
                                        <div class="alert alert-danger" style="margin-top: 10px">NIK tidak boleh kosong</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="name">Tanggal Project</label>
                                    <input type="date" class="form-control" name="project_date"
                                        value="{{ $projectDate }}">
                                    {{-- <input type="type" name="city" class="form-control @error('city') is-invalid @enderror" id="city" placeholder="Masukan Kota">
                            @error('city')
                                <div class="alert alert-danger" style="margin-top: 10px">Kota tidak boleh kosong</div>
                            @enderror --}}
                                </div>
                                <div class="form-group">
                                    <label for="name">Tanggal Lahir</label>
                                    <input type="date" name="birth"
                                        class="form-control @error('birth') is-invalid @enderror" id="birth"
                                        value="{{ $birth }}">
                                    @error('birth')
                                        <div class="alert alert-danger" style="margin-top: 10px">Tanggal Lahir tidak boleh
                                            kosong</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="name">No Telp</label>
                                    <input type="text" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror" id="phone"
                                        value="{{ $phone }}">
                                    @error('phone')
                                        <div class="alert alert-danger" style="margin-top: 10px">No. Telp tidak boleh kosong
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="name">Email</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror" id="email"
                                        value="{{ $email }}">
                                    @error('email')
                                        <div class="alert alert-danger" style="margin-top: 10px">Email tidak boleh kosong</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="card" style="width: 150px;height: 150px;background: gray">
                                            <img src="{{ url('/uploads/participants/' . $picture) }}" alt="your image"
                                                id="image-participant"
                                                style="width: 150px;height: 150px ; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; text-align: center;color: white">
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="name">Photo Asesi</label>
                                            <input type="file" name="image" accept="image/*" onchange="loadFile(event)"
                                                class="form-control image @error('image') is-invalid @enderror"
                                                id="image" value="{{ $picture }}">
                                            @error('image')
                                                <div class="alert alert-danger" style="margin-top: 10px">Photo Asesi tidak boleh
                                                    kosong</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 5px">
                                    <label for="name">Perusahaan</label>
                                    <select name="company" id="company"
                                        class="form-control @error('company') is-invalid @enderror">
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ $company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company')
                                        <div class="alert alert-danger" style="margin-top: 10px">Harus Memilih Perusahaan</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="division">Divisi</label>
                                    <select name="division" id="division"
                                        class="form-control @error('division') is-invalid @enderror">
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ $division_id == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                        <div class="alert alert-danger" style="margin-top: 10px">Harus Memilih Perusahaan</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="departement">Departemen</label>
                                    <select name="departement" id="departement"
                                        class="form-control @error('departement') is-invalid @enderror">
                                        @foreach ($departements as $departement)
                                            <option value="{{ $departement->id }}"
                                                {{ $departement_id == $departement->id ? 'selected' : '' }}>
                                                {{ $departement->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('departement')
                                        <div class="alert alert-danger" style="margin-top: 10px">Harus Memilih Perusahaan
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="position">Jabatan</label>
                                    <select name="position" id="position"
                                        class="form-control @error('position') is-invalid @enderror">
                                        <option value="0" disabled>== Pilih Jabatan ==</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ $position_id == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('position')
                                        <div class="alert alert-danger" style="margin-top: 10px">Harus Memilih Perusahaan
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="action" style="text-align: right">
                            <a href="/assessment/participants" class="btn btn-default">Kembali</a>
                            <button type="submit" class="btn btn-info">Ubah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        var loadFile = function(event) {
            var output = document.getElementById('image-participant');
            output.src = URL.createObjectURL(event.target.files[0]);
        };
    </script>
@endsection

@section('js')
    <script type="text/javascript">
        $(function() {});
    </script>
@endsection
