@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Ubah Jawaban Rater</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-header">
            <b>Jawaban Rater</b>
        </div>
        <div class="card-body">
            <form action="/assessment/project-participant-respondents/{{$projectParticipantRespondentAnswers[0]->project_participant_repondent_id}}" method="POST">
                @csrf
                @method('PUT')
                <table class="table table-bordered table-responsive">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">Kompetensi</th>
                            <th class="text-center">Perilaku</th>
                            <th class="text-center fit">{{strtoupper($projectParticipantRespondentAnswers[0]->type)}}</th>
                            <th class="text-center fit">{{strtoupper($projectParticipantRespondentAnswers[1]->type)}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($projectParticipantRespondentAnswers) - 1; $i+=2)
                            <tr>
                                <td>{{$projectParticipantRespondentAnswers[$i]->keyBehavior->competence->name}}</td>
                                <td>{{$projectParticipantRespondentAnswers[$i]->keyBehavior->description}}</td>
                                <td>
                                    <input type="number" class="form-control" min="1" max="10" maxlength="2" name="answers[]" value="{{$projectParticipantRespondentAnswers[$i]->answer}}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" min="1" max="10" maxlength="2" name="answers[]" value="{{$projectParticipantRespondentAnswers[$i+1]->answer}}">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <div class="text-right">
                    <a href="/assessment/project-participants/{{$projectParticipantRespondentAnswers[0]->projectParticipantRepondent->project_participant_id}}" class="btn btn-default">Kembali</a>
                    <input type="submit" value="Ubah" class="btn btn-info">
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .fit{
            width: 10%;
        }

        @media only screen and (max-width: 768px) {
            .fit{
                width: 15%;
            }
        }
    </style>
@endsection
