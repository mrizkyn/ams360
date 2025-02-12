@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Rater Proyek</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-header">
            <b>Rater Proyek</b>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Rater</th>
                        <th>Tipe Rater</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projectParticipantRespondents as $projectParticipantRespondent)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ $projectParticipantRespondent->respondent_name }}</td>
                            <td>{{ $projectParticipantRespondent->type }}</td>
                            <td class="text-center">
                                <a href="/assessment/project-participant-respondents/{{ $projectParticipantRespondent->id }}"
                                    class="btn btn-sm btn-default"><i class="far fa-eye"></i>Detail</a>
                                <a href="/assessment/project-participant-respondents/{{ $projectParticipantRespondent->id }}/edit"
                                    class="btn btn-sm btn-default"><i class="fas fa-edit"></i>Ubah</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-2 text-right">
                <a href="\assessment\projects\{{ $projectParticipant->project->id }}" class="btn btn-default">Kembali</a>
            </div>
        </div>
    </div>
@endsection
