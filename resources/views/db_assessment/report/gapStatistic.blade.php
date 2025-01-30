<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Statistik Gap</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .table-bordered td {
            border: 1px solid black;;
        }

        .table-bordered th {
            border: 1px solid black;;
        }

        .table-bordered{
            border: 1px solid black;;
        }

        .table-border{
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border: 1px solid black;
        }

        .table-border tr{
            border: 1px solid black;
        }

        .table-border th{
            border: 1px solid black;
        }

        .table-border td {
            border: 1px solid black;
            border-left: 0px solid;
            border-right: 0px solid;
            padding: .50rem;
            vertical-align: top;
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
            margin-bottom: 2%;
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
    <header>
        <img src="{{'storage/BPI.png'}}" width="180" height="90">
        <hr style="background-color: black">
    </header>

    <footer>
        <hr style="background-color: black">
    </footer>

    <div class="container-fluid">
        <div class="row text-center">
            <h3><b>STATISTIK GAP</b></h3>
        </div>
        <div class="row text-center">
            <h4><b>@foreach($project as $data){{$data->company->name}}@endforeach</b></h4>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <table class="table table-bordered text-center" width="50%">
                    <tr>
                        <td class="bg-dark" style="color: White;">Σ Kompetensi</td>
                        <td> {{count($gapValues)}}</td>
                    </tr>
                    <tr>
                        <td class="bg-dark" style="color: White;">Σ Asesi</td>
                        <td class="">{{$gapValues[0][0]->value + $gapValues[0][1]->value + $gapValues[0][2]->value}}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <h5>Nilai Statistik Gap</h5>
        </div>
        <div class="row">
            <table class="table table-bordered text-center ml-5 mr-5" width="50%">
                <tr>
                    <td rowspan="2" class="align-middle bg-warning"><b>Σ Kompetensi</b></td>
                    <td colspan="{{count($gapValues)}}" class="bg-warning col-sm-auto"><b>Kompetensi</b></td>
                </tr>
                <tr>
                    @foreach ($gapValues as $gapValue)
                        <td class="bg-warning">{{$gapValue[0]->competency}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &gt; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[0]->value}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &#61; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[1]->value}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &lt; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[2]->value}}</td>
                    @endforeach
                </tr>
            </table>
        </div>
        <div class="row">
            <h5>Nilai Persentasi Statistik Gap</h5>
        </div>
        <div class="row">
            <table class="table table-bordered text-center ml-5 mr-5">
                <tr>
                    <td rowspan="2" class="align-middle bg-warning"><b>Σ Kompetensi</b></td>
                    <td colspan="{{count($gapValues)}}" class="bg-warning"><b>Kompetensi</b></td>
                </tr>
                <tr>
                    @foreach ($gapValues as $gapValue)
                        <td class="bg-warning">{{$gapValue[0]->competency}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &gt; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[0]->percent}}%</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &#61; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[1]->percent}}%</td>
                    @endforeach
                </tr>
                <tr>
                    <td> &lt; Standar</td>
                    @foreach ($gapValues as $gapValue)
                        <td>{{$gapValue[2]->percent}}%</td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="row">
            <table class="table">
                <tr>
                    <td>
                        <img class="mt-5" src="{{$chart}}">
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

