@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Proyek</h1>
@stop

@section('content')
    <a href="/assessment/projects/{{$project->id}}/question-word" target="_blank" class="btn btn-primary mb-3">
        <i class="nav-item fas fa-file-word"></i> Cetak Kuesioner
    </a>
    {{-- <a href="/assessment/projects/{{$project->id}}/question-pdf" target="_blank" class="btn btn-danger mb-3">
        <i class="nav-item fas fa-file-pdf"></i> Cetak Kuesioner
    </a> --}}

    <div class="card">
        <div class="card-header">
            <b>Identitas Proyek</b>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <th>ID Proyek</th>
                        <td>{{$project->id}}</td>
                    </tr>
                    <tr>
                        <th>Nama Proyek</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Nama Perusahaan</th>
                        <td>{{$project->company->name}}</td>
                    </tr>
                    <tr>
                        <th>Sasaran Jabatan</th>
                        <td>{{$project->position->name}}</td>
                    </tr>
                    <tr>
                        <th>Waktu Awal</th>
                        <td>{{$project->start_date}}</td>
                    </tr>
                    <tr>
                        <th>Waktu Akhir</th>
                        <td>{{$project->end_date}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <b>Asesi Proyek</b>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Divisi</th>
                            <th>Departemen</th>
                            <th>Atasan</th>
                            <th>Rekan Kerja</th>
                            <th>Bawahan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectParticipants as $projectParticipant)
                            <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$projectParticipant->participant->identity_number}}</td>
                                <td>{{$projectParticipant->participant->name}}</td>
                                <td>{{$projectParticipant->participant->position->name}}</td>
                                <td>{{$projectParticipant->participant->division->name}}</td>
                                <td>{{$projectParticipant->participant->departement->name}}</td>
                                <td>{{$projectParticipant->superior_number}}</td>
                                <td>{{$projectParticipant->collegue_number}}</td>
                                <td>{{$projectParticipant->subordinate_number}}</td>
                                <td class="text-center">
                                    <a href="/assessment/project-participants/{{$projectParticipant->id}}" class="btn btn-sm btn-default"><i class="far fa-eye"></i>Detail</a>
                                    @if ($projectParticipant->status == "Belum Selesai")
                                        <a href="/assessment/project-participants/{{$projectParticipant->id}}/edit" class="btn btn-sm btn-default"><i class="fas fa-edit"></i>Ubah</a>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <b>Kuesioner</b>
        </div>

        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <th>Range Soal</th>
                        <td>{{$project->scale}}</td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        @if ($project->type == 1)
                            <td>Current Proficiency (CP) – Current Proficiency Required (CPR)</td>
                        @elseif($project->type == 2)
                            <td>Current Proficiency (CP)  – Future Proficiency Required (FPR)</td>
                        @elseif($project->type == 3)
                            <td>Current Frequency (CF) – Current Frequency Required (CFR)</td>
                        @elseif($project->type == 4)
                            <td>Current Frequency (CF) – Future Frequency Required (FFR)</td>
                        @endif
                    </tr>
                </tbody>
            </table>
            <br>
            <table class="table table-bordered fixed-header">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 1%">No</th>
                        <th style="width: 32%">Kompetensi</th>
                        <th>Perilaku Kunci</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projectQuestions as $projectQuestion)
                        <tr>
                            <td style="width: 1%">{{$loop->index + 1}}</td>
                            <td style="width: 32.6%">{{$projectQuestion->keyBehavior->competence->name}}</td>
                            <td>{{$projectQuestion->keyBehavior->description}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
