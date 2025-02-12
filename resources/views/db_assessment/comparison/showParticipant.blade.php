@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Komparasi Hasil Assessment</h1>
@stop

@section('content')
    @error('alert')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            <strong>{{ $message }}</strong>
        </div>
    @enderror
    <div class="card">
        <div class="card-body">
            <form action="/db-assessment/comparisons" method="POST">
                @csrf
                <div class="card card-widget collapsed-card">
                    <div class="card-header">
                        <h4 class="card-title">Data Asesi Perusahaan Pertama</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="first-participant-table">
                                <thead>
                                    <tr class="text-center">
                                        <th><input type="checkbox" id="select-all-first-participant"></th>
                                        <th>Nama Asesi</th>
                                        <th>Jabatan</th>
                                        <th>Nama Proyek</th>
                                        <th>Tanggal Awal</th>
                                        <th>Tanggal Akhir</th>
                                    </tr>
                                </thead>
                                <tbody id="first-participant-table-data">
                                    @foreach ($first_participants as $participantGroup)
                                        @foreach ($participantGroup as $participant)
                                            <tr>
                                                <td class="text-center"><input type="checkbox"
                                                        value="{{ $participant->id }}"
                                                        name="first_project_participant_ids[]"></td>
                                                <td>{{ $participant->participant->name }}</td>
                                                <td>{{ $participant->participant->position->name }}</td>
                                                <td>{{ $participant->project->name }}</td>
                                                <td>{{ $participant->project->start_date }}</td>
                                                <td>{{ $participant->project->end_date }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>

                <div class="card card-widget collapsed-card">
                    <div class="card-header">
                        <h4 class="card-title">Data Asesi Perusahaan Kedua</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="second-participant-table">
                                <thead>
                                    <tr class="text-center">
                                        <th><input type="checkbox" id="select-all-second-participant"></th>
                                        <th>Nama Asesi</th>
                                        <th>Jabatan</th>
                                        <th>Nama Proyek</th>
                                        <th>Tanggal Awal</th>
                                        <th>Tanggal Akhir</th>
                                    </tr>
                                </thead>
                                <tbody id="second-participant-table-data">
                                    @foreach ($second_participants as $participantGroup)
                                        @foreach ($participantGroup as $participant)
                                            <tr>
                                                <td class="text-center"><input type="checkbox"
                                                        value="{{ $participant->id }}"
                                                        name="second_project_participant_ids[]"></td>
                                                <td>{{ $participant->participant->name }}</td>
                                                <td>{{ $participant->participant->position->name }}</td>
                                                <td>{{ $participant->project->name }}</td>
                                                <td>{{ $participant->project->start_date }}</td>
                                                <td>{{ $participant->project->end_date }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>

                <input type="hidden" name="first_company_id" value="{{ $first_company_id }}">
                <input type="hidden" name="second_company_id" value="{{ $second_company_id }}">
                <input type="hidden" name="target_job_id" value="{{ $target_job_id }}">
                <input type="hidden" name="start_date" value="{{ $start_date }}">
                <input type="hidden" name="end_date" value="{{ $end_date }}">
                <input type="hidden" name="recommendation_type" value="{{ $recommendation_type }}">

                <input type="submit" class="btn btn-info float-right" value="Cetak">
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function() {
            $("#select-all-first-participant").click(function(e) {
                var table = $(e.target).closest('#first-participant-table');
                $('td input:checkbox', table).prop('checked', this.checked).change();
            });

            $("#select-all-second-participant").click(function(e) {
                var table = $(e.target).closest('#second-participant-table');
                $('td input:checkbox', table).prop('checked', this.checked).change();
            });
        });
    </script>
@endsection
