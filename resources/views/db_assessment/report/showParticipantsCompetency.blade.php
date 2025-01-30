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
                                @if (count($participant) == 2)
                                    <tr>
                                    <td class="text-center"><input type="checkbox" value="{{$participant[0]->participant_id}}" name="participant_ids[]"></td>
                                    <td>{{$participant[0]->participant->name}}</td>
                                    <td>{{$participant[0]->participant->position}}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <input type="hidden" value="{{$reportType}}" name="report_type">
                    <input type="hidden" value="{{$projectIds}}" name="projectIds">
                    <input type="hidden" value="{{$recommendationType}}" name="recommendationType">
                    <input type="hidden" name="participantValues" id="participantValues">
                    <input type="hidden" name="recommendationValues" id="recommendationValues">
                    <input type="hidden" name="competencyPopulationChart" id="competencyPopulationChart">
                    <input type="hidden" name="averageProgressChart" id="averageProgressChart">
                    <input type="hidden" name="recommendationChart" id="recommendationChart">
                </div>
                <div id="competencyPopulationChartDiv" class="chart"></div>
                <div id="averageProgressChartDiv" class="chart"></div>
                <div id="recommendationChartDiv" class="chart"></div>
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
                $.post("{{url('/data/dbassessment-participant-values')}}", $('#participant-form').serialize())
                .done(function(results) {
                    console.log(results);
                    $('#participantValues').val(JSON.stringify(results));

                    google.charts.load("45.2", {packages:['corechart']});

                    google.charts.setOnLoadCallback(competencyPopulationChart);
                    google.charts.setOnLoadCallback(averageProgressChart);

                    function competencyPopulationChart() {
                        var recommendationData = [['Kompetensi', 'Σ Asesi Naik' , 'Σ Asesi Turun' , 'Σ Asesi Tetap' , 'Σ Asesi Berubah']];

                        results[3].forEach(function(competencyPopulation){
                            recommendationData.push([competencyPopulation[0].competency, competencyPopulation[0].percent, competencyPopulation[1].percent, competencyPopulation[2].percent, competencyPopulation[3].percent]);
                        });

                        var data = google.visualization.arrayToDataTable(recommendationData);

                        var options = {
                            width:500,
                            height:400,
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

                        var chart = new google.visualization.ColumnChart(document.getElementById("competencyPopulationChartDiv"));

                        google.visualization.events.addListener(chart, 'ready', function () {
                            $('#competencyPopulationChart').val(chart.getImageURI());
                            getRecommendationData();
                        });

                        chart.draw(data, options);
                    }

                    function averageProgressChart() {
                        var averageProgressData = [['Type', 'Percent']];

                        results[5].forEach(function(averageProgress){
                            averageProgressData.push([averageProgress.type, averageProgress.percent]);
                        });

                        var data = google.visualization.arrayToDataTable(averageProgressData);

                        var options = {
                            width:500,
                            height:400,
                            vAxis: {
                                viewWindow: {
                                    min: 0,
                                    max: 100
                                },
                                ticks: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                            },
                            hAxis: {
                                textStyle: {fontSize: 10},
                            },
                            legend: {
                                position: 'none',
                            },
                        };

                        var chart = new google.visualization.ColumnChart(document.getElementById("averageProgressChartDiv"));

                        google.visualization.events.addListener(chart, 'ready', function () {
                            $('#averageProgressChart').val(chart.getImageURI());
                        });

                        chart.draw(data, options);
                    }


                });

                function getRecommendationData(){
                    $.post("{{url('/data/dbassessment-recommendation-progress')}}", $('#participant-form').serialize())
                .done(function(results) {
                    google.charts.load("45.2", {packages:['corechart']});

                    google.charts.setOnLoadCallback(recommendationChart);

                    function recommendationChart() {
                        header = ['Data'];
                        results[0].forEach(function(recommendation) {
                            header.push(recommendation.recommendation);
                        });

                        var recommendationData = [header];

                        for (let index = 0; index < results.length; index++) {
                            data = ['Data ' + (index + 1)];
                            for (let index2 = 0; index2 < results[index].length; index2++) {
                                data.push(results[index][index2].percent);
                            }
                            recommendationData.push(data);
                        }

                        var data = google.visualization.arrayToDataTable(recommendationData);

                        var options = {
                            width:500,
                            height:400,
                            vAxis: {
                                viewWindow: {
                                    min: 0,
                                    max: 100
                                },
                                ticks: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                            },
                        };

                        var chart = new google.visualization.ColumnChart(document.getElementById("recommendationChartDiv"));

                        google.visualization.events.addListener(chart, 'ready', function () {
                        $('#recommendationChart').val(chart.getImageURI());
                        $('#recommendationValues').val(JSON.stringify(results));
                        document.getElementById('participant-form').submit();
                    });

                        chart.draw(data, options);
                    }
                });
                }
            })

        });
    </script>
@endsection
