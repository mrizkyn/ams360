@extends('adminlte::page')

@section('title', 'Analisa Hasil Assessment Per Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Pilih Asesi yang akan ditampilkan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="/db-assessment/recapsPrint" method="POST" id="participant-form">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered" id="participant-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;"><input type="checkbox"
                                            id="select-all-participant"></th>
                                    <th style="width: 50%">Nama Asesi</th>
                                    <th style="width: 45%">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody id="participant-table-data">
                                @foreach ($participants as $participant)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" value="{{ $participant->id }}"
                                                name="participant_ids[]"></td>
                                        <td>{{ $participant->participant->name }}</td>
                                        <td>{{ $participant->participant->position['name'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div id="chartPie" class="chart"></div>
                    <div class="card-footer">
                        <div class="form-group text-right">
                            <a href="/db-assessment/recapsPrint" class="btn btn-default">Kembali</a>
                            <button type="button" class="btn btn-info" id="cetak">Cetak</button>
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

            $('#cetak').click(function() {
                $.post("{{ url('/db-assessment/showChart') }}", $('#participant-form').serialize())
                    .done(function(results) {
                        google.charts.load("45.2", {
                            packages: ['corechart']
                        });

                        google.charts.setOnLoadCallback(pieChart);

                        function pieChart() {
                            var pieData = [
                                ['Rekomendasi', 'Persentase']
                            ];

                            results.forEach(function(result) {
                                pieData.push([result.recommendation, parseFloat(result
                                    .percent)])
                            });


                            var data = google.visualization.arrayToDataTable(pieData);

                            var options = {
                                width: 500,
                                height: 400,
                            };

                            var chart = new google.visualization.PieChart(document.getElementById(
                                'chartPie'));

                            google.visualization.events.addListener(chart, 'ready', function() {
                                $('#pie_chart').val(chart.getImageURI());
                                $('#percent').val(JSON.stringify(results));

                                document.getElementById('participant-form').submit();
                            });

                            chart.draw(data, options);
                        }
                    });
            })
        });
    </script>
@endsection
