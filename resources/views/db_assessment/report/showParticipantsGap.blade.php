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
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 5%; text-align: center;"><input type="checkbox" id="select-all-participant"></th>
                                <th style="width: 50%">Nama Asesi</th>
                                <th style="width: 45%">Jabatan</th>

                            </tr>
                        </thead>
                        <tbody id="participant-table-data">
                            @foreach($participants as $participant)
                                <tr>
                                <td class="text-center"><input type="checkbox" value="{{$participant->id}}" name="participant_ids[]"></td>
                                <td>{{$participant->participant->name}}</td>
                                <td>{{$participant->participant->position}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <input type="hidden" value="{{$reportType}}" name="report_type">
                    <input type="hidden" value="{{$projectId}}" name="projectId">
                    <input type="hidden" name="chart" id="chart">
                    <input type="hidden" name="gapValue" id="gapValue">
                </div>
                <div id="div_chart" class="chart"></div>
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
    .chart{
        display: none;
    }
</style>
@endsection

@section('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function() {
            $("#select-all-participant").click(function(e){
                var table = $(e.target).closest('#participant-table');
                $('td input:checkbox',table).prop('checked',this.checked).change();
            });

            $('#cetak').click(function() {
                $.post("{{url('/db-assessment/showChartGap')}}", $('#participant-form').serialize())
                .done(function(results) {
                    $('#gapValue').val(JSON.stringify(results));

                    google.charts.load("45.2", {packages:['corechart']});
                    google.charts.setOnLoadCallback(drawCompanyChart);

                    function drawCompanyChart() {
                        chartData = [['Kompetensi', '> Standar', '= Standar', '< Standar']];
                        results.forEach(function(value) {
                            chartData.push([value[0].competency, value[0].percent, value[1].percent, value[2].percent]);
                        });

                        var data = google.visualization.arrayToDataTable(chartData);

                        var options = {
                            title: "Grafik Statistik Gap",
                            width: 500,
                            height: 400,
                            vAxis: {
                                viewWindow: {
                                    min: 0,
                                    max: 100
                                },
                                ticks: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                            },
                            hAxis: {
                                textStyle: {fontSize: 10},
                                slantedText: true,
                                slantedTextAngle: 90
                            },
                            legend: {
                                textStyle: {fontSize: 10},
                            },
                        };

                        var chart = new google.visualization.ColumnChart(document.getElementById('div_chart'));
                        google.visualization.events.addListener(chart, 'ready', function () {
                           $('#chart').val(chart.getImageURI());
                           $('#participant-form').submit();
                        });

                        chart.draw(data, options);
                    }
                });
            })

        });
    </script>
@endsection
