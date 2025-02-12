@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Rater Proyek</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-header">
            <b>Rater Proyek</b>
        </div>
        <div class="card-body">
            <form action="/assessment/project-participants/{{ $projectParticipant->id }}" method="POST">
                @csrf
                @method('PUT')
                <table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Divisi</th>
                            <th>Departemen</th>
                            <th class="fit">Atasan</th>
                            <th class="fit">Rekan Kerja</th>
                            <th class="fit">Bawahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>{{ $projectParticipant->participant->identity_number }}</td>
                            <td>{{ $projectParticipant->participant->name }}</td>
                            <td>{{ $projectParticipant->participant->position->name }}</td>
                            <td>{{ $projectParticipant->participant->division->name }}</td>
                            <td>{{ $projectParticipant->participant->departement->name }}</td>
                            <td>
                                <input type="number" class="form-control" maxlength="2" name="superior_number"
                                    value="{{ $projectParticipant->superior_number }}" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" maxlength="2" name="collegue_number"
                                    value="{{ $projectParticipant->collegue_number }}" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" maxlength="2" name="subordinate_number"
                                    value="{{ $projectParticipant->subordinate_number }}" required>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group text-right">
                    <a href="\assessment\projects\{{ $projectParticipant->project->id }}"
                        class="btn btn-default">Kembali</a>
                    <input class="btn btn-info" type="submit" value="Ubah">
                </div>
            </form>
        </div>
    </div>
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
