<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Statistik Rekomendasi</title>
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
            <h3><b>STATISTIK REKOMENDASI</b></h3>
        </div>
        <div class="row text-center">
            <h3><b>@foreach($project as $data){{$data->company->name}}@endforeach</b></h3>
        </div>
        <div class="row">
            <p>N(Jumlah Asesi) = 
                @php
                    $total = 0; 
                    foreach ($percent as $data) {
                        $total += $data->jumlah;
                    }
                    echo $total;
                @endphp
            </p>    
        </div>
        
        <div class="row">
            <table class="table table-bordered text-center">
                <tr>
                    <th>Rekomendasi</th>
                    <th>Jumlah</th>
                    <th>%</th>
                </tr>

                @foreach ($percent as $data)
                    <tr>
                        <td>{{ $data->recommendation }}</td>
                        <td>{{ $data->jumlah }}</td>
                        <td>{{ $data->percent }} %</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="row">
            <table class="table">
                <tr>
                    <td class="text-center"><img src="{{$chart}}"></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>