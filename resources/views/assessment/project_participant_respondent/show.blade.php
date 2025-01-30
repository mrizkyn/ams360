@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Jawaban Rater</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-header">
            <b>Jawaban Rater</b>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
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
                            <td>{{$projectParticipantRespondentAnswers[$i]->answer}}</td>
                            <td>{{$projectParticipantRespondentAnswers[$i+1]->answer}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
            <div class="mt-2 text-right">
                <a href="/assessment/project-participants/{{$projectParticipantRespondentAnswers[0]->projectParticipantRepondent->project_participant_id}}" class="btn btn-default">Kembali</a>
            </div>
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
