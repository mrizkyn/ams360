<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Statistik Gap</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        td {
            position: relative;
            padding: 10px;
        }

        td span{
            display:inline-table;
            writing-mode: tb-rl;
            white-space:pre;
        }

        div p, td{
            font-family: DejaVu Sans;
            font-size: 14px;
        }
        .indent {
            text-indent: 50px;
        }
        @page {
            margin: 130px 50px;
        }

        header {
            float: right;
            position: fixed;
            top: -90px;
            height: 90px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            height: 40px;
            font-family: DejaVu Sans;
            font-size: 10px;
        }
        .pagenum:before {
            content:"Hal. " counter(page);
        }
        .page-break {
            page-break-inside:avoid;
            page-break-after: always;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    {{-- <header>
        <img src="{{'storage/BPI.png'}}" width="180" height="90">
        <hr style="background-color: black">
    </header>

    <footer>
        <hr style="background-color: black">
    </footer> --}}

    @php
        $competencyCount = count($participantValues[1][0]);
        $x = 0;
        $y = 0;
    @endphp

    <div class="container-fluid">
        <div class="row text-center">
            <h3><b>STATISTIK PERKEMBANGAN KOMPETENSI</b></h3>
        </div>

        <div class="row">
            <table class="table table-bordered text-center w-50">
                <tr>
                    <td class="bg-dark" style="color: White;">Σ Kompetensi</td>
                    <td> {{$competencyCount}}</td>
                </tr>
                <tr>
                    <td class="bg-dark" style="color: White;">Σ Asesi</td>
                    <td>{{count($participants)}}</td>
                </tr>
            </table>
        </div>
        <div class="row">
            <h5>Data Komparasi</h5>
        </div>
        <div class="row">
            <table class="table table-bordered text-center">
                <tr>
                    <td rowspan="2" class="align-middle bg-warning"><b>No</b></td>
                    <td rowspan="2" class="align-middle bg-warning"><b>Nama</b></td>
                    <td rowspan="2" class="align-middle bg-warning"><b>Jabatan Saat Ini</b></td>
                    <td colspan="{{$competencyCount + 1}}" class="bg-warning"><b>Data II</b></td>
                    <td colspan="{{$competencyCount + 1}}" class="bg-primary"><b>Data I</b></td>
                </tr>
                <tr>
                    @foreach ($participantValues[1][0] as $competency)
                        <td class="bg-warning"><span>{{$competency->competency}}</span></td>
                    @endforeach
                    <td class="bg-dark"><span>Rata-Rata</span></td>

                    @foreach ($participantValues[1][0] as $competency)
                        <td class="bg-primary"><span>{{$competency->competency}}</span></td>
                    @endforeach
                    <td class="bg-dark"><span>Rata-Rata</span></td>
                </tr>

                @foreach ($participantValues[0] as $value)
                    <tr>
                        <td>{{$x + 1}}</td>
                        <td>{{$participants[$x]->name}}</td>
                        <td>{{$participants[$x]->position}}</td>
                        @for ($i = 0; $i < $competencyCount; $i++)
                            <td>{{$value[$i]->value}}</td>
                        @endfor

                        <td>{{$participantValues[4][$x][0]}}</td>

                        @for ($j = $i; $j < $competencyCount * 2; $j++)
                            <td>{{$value[$j]->value}}</td>
                        @endfor

                        <td>{{$participantValues[4][$x][1]}}</td>
                    </tr>

                    @php
                        $x++;
                    @endphp
                @endforeach
            </table>
        </div>

        <div class="row">
            <h5>Perkembangan Kompetensi Per Individu</h5>
        </div>

        <div class="row">
            <table class="table table-bordered text-center">
                <tr>
                    <td rowspan="2" class="align-middle bg-warning"><b>No</b></td>
                    <td rowspan="2" class="align-middle bg-warning"><b>Nama</b></td>
                    <td rowspan="2" class="align-middle bg-warning"><b>Jabatan Saat Ini</b></td>
                    <td colspan="{{$competencyCount + 1}}" class="bg-warning"><b>Perkembangan (Data I s/d Data II)</b></td>
                    <td colspan="4" class="bg-warning"><b>Σ Nilai Kompetensi</b></td>
                    <td colspan="4" class="bg-warning"><b>% Nilai Kompetensi</b></td>
                </tr>
                <tr>
                    @foreach ($participantValues[1][0] as $competency)
                        <td class="bg-warning"><span>{{$competency->competency}}</span></td>
                    @endforeach
                    <td class="bg-dark"><span>Rata-Rata</span></td>

                    <td class="bg-warning"><span>Naik</span></td>
                    <td class="bg-warning"><span>Turun</span></td>
                    <td class="bg-warning"><span>Tetap</span></td>
                    <td class="bg-warning"><span>Berubah</span></td>

                    <td class="bg-warning"><span>Naik</span></td>
                    <td class="bg-warning"><span>Turun</span></td>
                    <td class="bg-warning"><span>Tetap</span></td>
                    <td class="bg-warning"><span>Berubah</span></td>
                </tr>

                @foreach ($participantValues[0] as $value)
                    <tr>
                        <td>{{$y + 1}}</td>
                        <td>{{$participants[$y]->name}}</td>
                        <td>{{$participants[$y]->position}}</td>
                        @foreach ($participantValues[1][$y] as $item)
                            <td>{{$item->value}}</td>
                        @endforeach
                        <td>{{collect($participantValues[1][$y])->average('value')}}</td>
                        @foreach ($participantValues[2][$y] as $item)
                            <td>{{$item->value}}</td>
                        @endforeach
                        @foreach ($participantValues[2][$y] as $item)
                            <td>{{number_format((float)$item->percent, 2, '.', '')}}%</td>
                        @endforeach
                    </tr>

                    @php
                        $y++;
                    @endphp
                @endforeach
            </table>
        </div>

        <div class="row">
            <h5>Perkembangan Kompetensi Per Kompetensi Populasi</h5>
        </div>

            <div class="row">
                <table class="table table-bordered text-center">
                    <tr>
                        <td rowspan="2" class="align-middle bg-warning"><b>Σ Asesi</b></td>
                        <td colspan="{{$competencyCount}}" class="bg-warning"><b>Kompetensi</b></td>
                    </tr>
                    <tr>
                        @foreach ($participantValues[1][0] as $competency)
                            <td class="bg-warning"><span>{{$competency->competency}}</span></td>
                        @endforeach
                    </tr>

                    <tr>
                        <td>Σ Asesi Naik</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{$value[0]->value}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Σ Asesi Turun</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{$value[1]->value}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Σ Asesi Tetap</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{$value[2]->value}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>Σ Asesi Berubah</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{$value[3]->value}}</td>
                        @endforeach
                    </tr>
                </table>
            </div>
            <div class="row">
                <table class="table table-bordered text-center">
                    <tr>
                        <td rowspan="2" class="align-middle bg-warning"><b>% Asesi</b></td>
                        <td colspan="{{$competencyCount}}" class="bg-warning"><b>Kompetensi</b></td>
                    </tr>
                    <tr>
                        @foreach ($participantValues[1][0] as $competency)
                            <td class="bg-warning"><span>{{$competency->competency}}</span></td>
                        @endforeach
                    </tr>

                    <tr>
                        <td>% Asesi Naik</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{number_format((float)$value[0]->percent, 2, '.', '')}}%</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>% Asesi Turun</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{number_format((float)$value[1]->percent, 2, '.', '')}}%</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>% Asesi Tetap</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{number_format((float)$value[2]->percent, 2, '.', '')}}%</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td>% Asesi Berubah</td>
                        @foreach ($participantValues[3] as $value)
                            <td>{{number_format((float)$value[3]->percent, 2, '.', '')}}%</td>
                        @endforeach
                    </tr>
                </table>
            </div>

            <div class="row">
                <img src="{{$competencyPopulationChart}}">
            </div>

            <div class="row">
                <h5>Perkembangan Nilai Rata-Rata Data I s/d Data II</h5>
            </div>

            <div class="row">
                <div class="col-6">
                    <table class="table table-bordered text-center">
                        <tr>
                            <td class="bg-dark"><b>Nilai Rata-Rata</b></td>
                            <td class="bg-dark"><b>Σ</b></td>
                            <td class="bg-dark"><b>%</b></td>
                        </tr>
                        @foreach ($participantValues[5] as $item)
                            <tr>
                                <td>{{$item->type}}</td>
                                <td>{{$item->value}}</td>
                                <td>{{number_format((float)$item->percent, 2, '.', '')}}%</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <div class="col-6">
                    <img src="{{$averageProgressChart}}">
                </div>
            </div>

                <div class="row">
                    <h5>Perubahan Rekomendasi Data I sd Data II</h5>
                </div>

                <div class="row">
                        <table class="table table-bordered text-center">
                            <tr>
                                <td class="bg-dark"><b>Katergori</b></td>
                                <td class="bg-dark"><b>Data I</b></td>
                                <td class="bg-dark"><b>Data II</b></td>
                            </tr>

                            @foreach ($recommendationValues[0] as $key => $item)
                                <tr>
                                    <td>{{$item->recommendation}}</td>
                                    @foreach ($recommendationValues as $value)
                                        <td>{{$value[$key]->value}}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                </div>

                <div class="row">
                        <table class="table table-bordered text-center">
                            <tr>
                                <td class="bg-dark"><b>Katergori</b></td>
                                <td class="bg-dark"><b>Data I</b></td>
                                <td class="bg-dark"><b>Data II</b></td>
                            </tr>

                            @foreach ($recommendationValues[0] as $key => $item)
                                <tr>
                                    <td>{{$item->recommendation}}</td>
                                    @foreach ($recommendationValues as $value)
                                        <td>{{number_format((float)$value[$key]->percent, 2, '.', '')}}%</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    </div>


                    <div class="col-6">
                        <img src="{{$recommendationChart}}">
                    </div>
                </div>
    </div>
</body>
</html>

