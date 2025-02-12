@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Buat Proyek</h1>
@stop

@section('css')
    <style>
        .fixed-header {
            table-layout: fixed;
            border-collapse: collapse;
        }

        .fixed-header tbody {
            display: block;
            width: 100%;
            overflow: auto;
            height: 320px;
        }

        .fixed-header thead tr {
            display: table;
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <form action="{{ url('assessment/projects') }}" method="POST" autocomplete="off">
        @csrf
        <div class="card">
            <div class="card-header">
                <b>Identitas Proyek</b>
            </div>
            <div class="card-body">
                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }} ">
                    <label for="name">Nama Proyek</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="Masukan Nama Proyek" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group {{ $errors->has('company_id') ? ' has-error' : '' }}">
                    <label for="name">Nama Perusahaan</label>
                    <select name="company_id" id="company_id" class="form-control" value="{{ old('company_id') }}" required>
                        <option value="" disabled selected>Pilih Nama Perusahaan</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <div class="alert alert-danger" style="margin-top: 10px">Perusahaan belum dipilih</div>
                    @enderror
                </div>
                <div class="form-group {{ $errors->has('position_id') ? ' has-error' : '' }}">
                    <label for="name">Sasaran Jabatan</label>
                    <select name="position_id" id="position_id" class="form-control" value="{{ old('position_id') }}"
                        required>
                        <option value="" disabled selected>Pilih Target Jabatan</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}"
                                {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                        @endforeach
                    </select>
                    @error('position_id')
                        <div class="alert alert-danger" style="margin-top: 10px">Target Jabatan belum dipilih</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="start_date">Tanggal Awal</label>
                    <input type="date" class="form-control" name="start_date" placeholder="Masukan Tanggal Awal"
                        value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <div class="alert alert-danger" style="margin-top: 10px">Tanggal awal belum dipilih belum dipilih</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="end_date">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" placeholder="Masukan Tanggal Akhir"
                        value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <div class="alert alert-danger" style="margin-top: 10px">Tanggal akhir belum dipilih belum dipilih</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <b>Asesi Proyek</b>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="participant-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-participant"></th>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Jabatan</th>
                                <th>Divisi</th>
                                <th>Departemen</th>
                                <th>Atasan</th>
                                <th>Rekan Kerja</th>
                                <th>Bawahan</th>
                            </tr>
                        </thead>
                        <tbody id="participant-table-data">

                        </tbody>
                    </table>
                    @if (session('errorAsesi'))
                        <div class="alert alert-danger" style="margin-top: 10px">Asesi belum dipilih</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <b>Setup Kuesioner</b>
            </div>
            <div class="card-body">
                <input name="scale" value="10" type="hidden">

                <div class="form-group">
                    <label for="type">Tipe</label>
                    <select name="type" class="form-control" required>
                        <option value="" selected disabled>Pilih Tipe Kuesioner</option>
                        <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>Current Proficiency (CP) –
                            Current Proficiency Required (CPR)</option>
                        <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Current Proficiency (CP) – Future
                            Proficiency Required (FPR)</option>
                        <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Current Frequency (CF) – Current
                            Frequency Required (CFR)</option>
                        <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>Current Frequency (CF) – Future
                            Frequency Required (FFR)</option>
                    </select>
                </div>

                <table class="table table-bordered fixed-header" id="key-behavior-table">
                    <thead>
                        <tr>
                            <th style="width: 1%"><input type="checkbox" id="select-all-key-behavior"></th>
                            <th style="width: 32%">Kompetensi</th>
                            <th>Perilaku Kunci</th>
                        </tr>
                    </thead>
                    <tbody id="key-behavior-table-data">
                        @foreach ($keyBehaviors as $keyBehavior)
                            <tr>
                                <td style="width: 1%"><input type="checkbox" data-id="{{ $keyBehavior->id }}"></th>
                                <td style="width: 32.6%">{{ $keyBehavior->competence->name }}</td>
                                <td>{{ $keyBehavior->description }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                @if (session('errorKeyBehaviour'))
                    <div class="alert alert-danger" style="margin-top: 10px">Perilaku Kunci belum dipilih</div>
                @endif
            </div>
        </div>

        <input type="hidden" name="selected_participants" id="selectedParticipants">
        <input type="hidden" name="selected_key_behaviors" id="selectedKeyBehaviors">

        <div class="form-group text-right">
            <a href="\assessment\projects" class="btn btn-default">Kembali</a>
            <input class="btn btn-info" type="submit" value="Simpan" id="simpan">
        </div>
    </form>
@stop

@section('css')
    <style>
        .number {
            width: 100%;
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $("#company_id").change(function() {
                $("#participant-table-data").empty();
                $.get("{{ url('data/assessment-project-participant-data') }}" + "/" + $("#company_id")
                    .val(),
                    function(data, status) {
                        participants = data.data;
                        participants.forEach(participant => {
                            $("#participant-table-data").append(
                                `<tr>
                            <td><input type="checkbox" class="checkbox" data-id="${participant.id}"></th>
                            <td>${participant.identity_number}</th>
                            <td>${participant.name}</th>
                            <td>${participant.position}</th>
                            <td>${participant.division}</th>
                            <td>${participant.departement}</th>
                            <td><input type="number" maxlength="2" class="number superior_number" id="superior_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number collegue_number" id="collegue_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number subordinate_number" id="subordinate_number_${participant.id}" disabled></th>
                            </tr>`
                            )
                        });
                        $(document).on("change", "td input:checkbox", function() {
                            $(this).prop('checked', this.checked);
                        });
                    });
            });

            $('#simpan').click(function() {
                var selectedParticipants = [];
                var selectedKeyBehaviors = [];
                $("#participant-table tr").each(function(index, row) {
                    td = $('td', this);
                    if ($('input:checkbox', td).prop('checked')) {
                        var participant = {
                            id: $('input:checkbox', td).attr("data-id"),
                            superior_number: $('.superior_number', this).val(),
                            collegue_number: $('.collegue_number', this).val(),
                            subordinate_number: $('.subordinate_number', this).val()
                        }
                        selectedParticipants.push(participant);
                    }
                })

                $("#key-behavior-table td input:checkbox").each(function(index, row) {
                    if ($(this).prop('checked')) {
                        var keyBehavior = {
                            id: $(this).attr("data-id")
                        }
                        selectedKeyBehaviors.push(keyBehavior);
                    }
                })

                $("#selectedParticipants").val(JSON.stringify(selectedParticipants));
                $("#selectedKeyBehaviors").val(JSON.stringify(selectedKeyBehaviors));
            });

            $("#select-all-participant").click(function(e) {
                var table = $(e.target).closest('#participant-table');
                $('td input:checkbox', table).click();
            });

            $("#select-all-key-behavior").click(function(e) {
                var table = $(e.target).closest('#key-behavior-table');
                $('td input:checkbox', table).click();
            });

            $(document).on('click', '.checkbox', function() {
                var id = $(this).attr('data-id')
                if (this.checked) {
                    document.getElementById('superior_number_' + id).setAttribute('required', 'required')
                    document.getElementById('collegue_number_' + id).setAttribute('required', 'required')
                    document.getElementById('subordinate_number_' + id).setAttribute('required', 'required')

                    document.getElementById('superior_number_' + id).removeAttribute('disabled')
                    document.getElementById('collegue_number_' + id).removeAttribute('disabled')
                    document.getElementById('subordinate_number_' + id).removeAttribute('disabled')
                } else {
                    document.getElementById('superior_number_' + id).setAttribute('disabled', 'disabled')
                    document.getElementById('collegue_number_' + id).setAttribute('disabled', 'disabled')
                    document.getElementById('subordinate_number_' + id).setAttribute('disabled', 'disabled')

                    document.getElementById('superior_number_' + id).removeAttribute('required')
                    document.getElementById('collegue_number_' + id).removeAttribute('required')
                    document.getElementById('subordinate_number_' + id).removeAttribute('required')
                }
            })
        });
    </script>
@stop
