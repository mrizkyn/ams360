@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #3674B5;">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-4x"></i>
                    <h5 class="card-title mt-2">Jumlah User</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #578FCA;">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-4x"></i>
                    <h5 class="card-title mt-2">Jumlah Perusahaan</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark" style="background-color: #A1E3F9;">
                <div class="card-body text-center">
                    <i class="fas fa-folder-open fa-4x"></i>
                    <h5 class="card-title mt-2">Jumlah Proyek</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card text-dark" style="background-color: #90C8E0;">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-4x"></i>
                    <h5 class="card-title mt-2">Proyek Aktif</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #2F6690;">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-4x"></i>
                    <h5 class="card-title mt-2">Proyek Selesai</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #4A86B3;">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-4x"></i>
                    <h5 class="card-title mt-2">Proyek Hampir Deadline</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #78A9D9;">
                <div class="card-body text-center">
                    <i class="fas fa-user-graduate fa-4x"></i>
                    <h5 class="card-title mt-2">Jumlah Asesi</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark" style="background-color: #B6D8E6;">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-4x"></i>
                    <h5 class="card-title mt-2">Asesi Belum di Nilai</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background-color: #92C6D9;">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-4x"></i>
                    <h5 class="card-title mt-2">Jumlah Rater</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card text-dark" style="background-color: #A5D4E0;">
                <div class="card-body text-center">
                    <i class="fas fa-hourglass-half fa-4x"></i>
                    <h5 class="card-title mt-2">Rater Belum Menilai</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="company">Nama Perusahaan</label>
                        <select class="form-control" name="company" id="company">
                            <option value="0" selected disabled>Pilih nama perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="project">Nama Proyek</label>
                        <select class="form-control" name="project" id="project">
                            <option value="0" selected disabled>Pilih nama proyek</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="detail">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">Tabel</div>
                        <div class="card-body">
                            <table class="table table-bordered tale-responsive" id="participant-table">
                                <thead id="participant-header" class="table-dark">
                                    <tr>
                                        <td>Nama</td>
                                        <td>Status</td>
                                    </tr>
                                </thead>
                                <tbody id="participant-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">Grafik</div>
                        <div class="card-body">
                            <div id="chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var projects;
            $('#detail').hide();

            $('#company').change(function() {
                $.get("{{ url('data/assessment-project-data') }}" + "/" + $("#company").val(), function(
                    data) {
                    $('#project').empty();
                    $('#project').append(
                        `<option value="0" selected disabled>Pilih nama projek</option>`);
                    data.forEach(project => {
                        $('#project').append(
                            `<option value="${project.id}">${project.name}</option>`);
                    });
                });
            });

            $('#project').change(function() {
                $.get("{{ url('data/assessment-projectparticipant-data') }}" + "/" + $("#project").val(),
                    function(data) {
                        var totalParticipants = data.length;
                        var notFinishedParticipants = 0;
                        google.charts.load('current', {
                            packages: ['corechart', 'bar']
                        });
                        google.charts.setOnLoadCallback(drawChart);

                        $('#participant-body').empty();
                        data.forEach(participant => {
                            $('#participant-body').append(
                                `<tr>
                                <td>${participant.participant.name}</td>
                                <td>${participant.status}</td>
                            </tr>`
                            );
                            if (participant.status == 'Belum Selesai') {
                                notFinishedParticipants++;
                            }
                        });

                        totalParticipants = totalParticipants - notFinishedParticipants;

                        function drawChart() {
                            var array = google.visualization.arrayToDataTable([
                                ['Status', 'Selesai', 'Belum Selesai'],
                                [
                                    'Participant',
                                    totalParticipants,
                                    notFinishedParticipants,
                                ],
                            ]);

                            var options = {
                                chart: {
                                    title: 'Status Assessment',
                                },
                                colors: ['#007aff', '#f96063']
                            };

                            var chart = new google.charts.Bar(document.getElementById('chart'));

                            chart.draw(array, google.charts.Bar.convertOptions(options));
                        }
                    });
                $('#detail').show();
            });

        });
    </script>
@endsection
