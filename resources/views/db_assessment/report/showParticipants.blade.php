@extends('adminlte::page')

@section('title', 'Analisa Hasil Assessment Per Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Pilih Asesi yang akan ditampilkan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
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
                <form action="/db-assessment/recapsPrint" method="POST" id="participant-form">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered" id="participant-table">
                            <thead>
                                <tr class="text-center">
                                    <th><input type="checkbox" id="select-all-participant"></th>
                                    <th>Nama Asesi</th>
                                    <th>Jabatan</th>
                                    <th>Nama Proyek</th>
                                    <th>Tanggal Awal</th>
                                    <th>Tanggal Akhir</th>
                                </tr>
                            </thead>
                            <tbody id="participant-table-data">
                                @foreach ($participants as $participantGroup)
                                    @foreach ($participantGroup as $participant)
                                        <tr>
                                            <td class="text-center"><input type="checkbox" value="{{ $participant->id }}"
                                                    name="project_participant_ids[]"></td>
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
                        <input type="hidden" name="company_id" value="{{ $company_id }}">
                        <input type="hidden" name="target_job_id" value="{{ $target_job_id }}">
                        <input type="hidden" name="start_date" value="{{ $start_date }}">
                        <input type="hidden" name="end_date" value="{{ $end_date }}">
                        <input type="hidden" name="report_type" value="{{ $report_type }}">
                        <input type="hidden" name="recommendation_id" value="{{ $recommendation_id }}">
                    </div>
                    <div class="card-footer">
                        <div class="form-group text-right">
                            <a href="/db-assessment/recapsPrint" class="btn btn-default">Kembali</a>
                            <input type="submit" class="btn btn-info" value="Cetak">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style type="text/css">
        .chart {
            display: none;
        }
    </style>
@endsection

@section('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function() {
            $("#select-all-participant").click(function(e) {
                var table = $(e.target).closest('#participant-table');
                $('td input:checkbox', table).prop('checked', this.checked).change();
            });
        });
    </script>
@endsection
