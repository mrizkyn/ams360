@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Assessment</h1>
@stop


@section('content')
    <form action="{{ url('assessment/assessments') }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group {{ $errors->has('project_id') ? ' has-error' : '' }}">
                            <label for="name">Nama Proyek</label>
                            <select name="project_id" id="project_id" class="form-control" required>
                                <option value="" disabled selected>Pilih Nama Proyek</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <label>Asesi</label>
                    </div>
                    <div class="card-body">
                        <div class="form-group {{ $errors->has('assesion_name') ? ' has-error' : '' }}">
                            <label for="assesion_name">Nama</label>
                            <select name="project_participant_id" id="participant_id" class="form-control" disabled
                                required>
                                <option value="" selected disabled>Pilih Nama Asesi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" id="position" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Divisi</label>
                            <input type="text" id="division" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Departemen</label>
                            <input type="text" id="departement" class="form-control" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <label>Rater</label>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="rater_type">Rater</label>
                            <select name="rater_type" id="rater_type" class="form-control" required disabled>
                                <option value="" selected disabled>Pilih Jenis Rater</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rater_name">Nama</label>
                            <input type="text" list="rater_name_data" name="rater_name" id="rater_name"
                                class="form-control" value="{{ old('rater_name') }}" placeholder="Pilih Nama Rater"
                                disabled>
                            <datalist id="rater_name_data">

                            </datalist>
                            @if ($errors->has('rater_name'))
                                <span class="text-danger">
                                    {{ $errors->first('rater_name') }}
                                </span>
                            @endif
                        </div>
                        {{-- <div class="form-group">
                            <label for="rater_name">Nama</label>
                            <input type="text" list="rater_name_data" name="rater_name" id="rater_name" class="form-control" value="{{old('rater_name')}}" placeholder="Pilih Nama Rater" disabled>
                            <datalist id="rater_name_data">

                            </datalist>
                            @if ($errors->has('rater_name'))
                            <span class="text-danger">
                                {{$errors->first('rater_name')}}
                            </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="rater_type">Rater</label>
                            <select name="rater_type" id="rater_type" class="form-control" required disabled>
                                <option value="" selected disabled>Pilih Jenis Rater</option>
                            </select>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <label>Tabel Assessment</label>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="question-table">
                                <thead id="question_header">

                                </thead>
                                <tbody id="question_body">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col text-right">
                <input type="hidden" name="answers" id="answers">
                <input class="btn btn-info" type="submit" id="submit" value="Simpan">
            </div>
        </div>
    </form>
@endsection

@section('css')
    <style>
        .fit {
            width: 10%;
        }

        @media only screen and (max-width: 768px) {
            .fit {
                width: 15%;
            }
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var assesions;

            $("#project_id").change(function() {
                $.get("{{ url('data/assessment-assesion-data') }}" + "/" + $("#project_id").val(), function(
                    data) {
                    assesions = data;
                    $('#participant_id').empty();
                    $('#participant_id').append(
                        `<option value="" selected disabled>Pilih Nama Asesi</option>`);
                    assesions.forEach(assesion => {
                        $('#participant_id').append(
                            `<option value="${assesion.id}">${assesion.name}</option>`);
                    });
                    $('#participant_id').removeAttr('disabled');
                });

                $.get("{{ url('data/assessment-question-data') }}" + "/" + $("#project_id").val(), function(
                    data) {
                    questions = data.data;
                    switch (questions[0].type) {
                        case "1":
                            $('#question_header').empty();
                            $('#question_header').append(
                                `<tr>
                                <th class="text-center">Kompetensi</th>
                                <th class="text-center">Perilaku</th>
                                <th class="text-center fit">CPR</th>
                                <th class="text-center fit">CP</th>
                            </tr>`
                            )

                            $('#question_body').empty();
                            questions.forEach(question => {
                                $('#question_body').append(
                                    `<tr>
                                    <td>${question.competency}</td>
                                    <td>${question.key_behavior}</td>
                                    <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cpr"></td>
                                    <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cp"></td>
                                </tr>`
                                )
                            });
                            break;
                        case "2":
                            $('#question_header').empty();
                            $('#question_header').append(
                                `<tr>
                                    <th class="text-center">Kompetensi</th>
                                    <th class="text-center">Perilaku</th>
                                    <th class="text-center fit">FPR</th>
                                    <th class="text-center fit">CP</th>
                                </tr>`
                            )

                            $('#question_body').empty();
                            questions.forEach(question => {
                                $('#question_body').append(
                                    `<tr>
                                        <td>${question.competency}</td>
                                        <td>${question.key_behavior}</td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="fpr"></td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cp"></td>
                                    </tr>`
                                )
                            });
                            break;
                        case "3":
                            $('#question_header').empty();
                            $('#question_header').append(
                                `<tr>
                                    <th class="text-center">Kompetensi</th>
                                    <th class="text-center">Perilaku</th>
                                    <th class="text-center fit">CFR</th>
                                    <th class="text-center fit">CF</th>
                                </tr>`
                            )

                            $('#question_body').empty();
                            questions.forEach(question => {
                                $('#question_body').append(
                                    `<tr>
                                        <td>${question.competency}</td>
                                        <td>${question.key_behavior}</td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cfr"></td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cf"></td>
                                    </tr>`
                                )
                            });
                            break;
                        case "4":
                            $('#question_header').empty();
                            $('#question_header').append(
                                `<tr>
                                    <th class="text-center">Kompetensi</th>
                                    <th class="text-center">Perilaku</th>
                                    <th class="text-center fit">FFR</th>
                                    <th class="text-center fit">CF</th>
                                </tr>`
                            )

                            $('#question_body').empty();
                            questions.forEach(question => {
                                $('#question_body').append(
                                    `<tr>
                                        <td>${question.competency}</td>
                                        <td>${question.key_behavior}</td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="ffr"></td>
                                        <td><input type="number" class="form-control" min="1" max="${question.scale}" required data_id="${question.id}" data_type="cf"></td>
                                    </tr>`
                                )
                            });
                            break;
                    }
                });
            })

            $("#participant_id").change(function() {
                assesions.forEach(assesion => {
                    if (assesion.id == $('#participant_id').val()) {
                        $('#position').val(assesion.position);
                        $('#division').val(assesion.division);
                        $('#departement').val(assesion.departement);
                        $.get("{{ url('data/assessment-assesion-type-data') }}" + "/" + assesion.id,
                            function(data) {
                                $('#rater_type').empty();
                                $('#rater_type').append(
                                    '<option value="" selected disabled>Pilih Jenis Rater</option>'
                                    );
                                data.forEach(status => {
                                    $('#rater_type').append(
                                        `<option value="${status.type}">${status.type}</option>`
                                        );
                                })
                                $('#rater_type').removeAttr('disabled');
                            });
                    }
                })
            });

            $('#rater_type').change(function() {
                $('#rater_name').removeAttr('disabled');
                $('#rater_name').val('');

                if ($(this).val() == 'Diri Sendiri') {
                    $('#rater_name').val($('#participant_id option:selected').text());
                }
            })

            $("#submit").click(function() {
                var answers = [];
                $("#question-table td input").each(function(index, row) {
                    var answer = {
                        id: $(this).attr('data_id'),
                        type: $(this).attr('data_type'),
                        value: $(this).val()
                    }
                    answers.push(answer);
                })

                $("#answers").val(JSON.stringify(answers));
            })
        });
    </script>

@endsection
