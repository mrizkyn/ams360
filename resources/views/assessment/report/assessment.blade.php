@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Laporan Hasil Assessment</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Laporan Assessment</h3>
        </div>
        <div class="card-body">
            <form id="report-assessment-form" action="report-assessment" method="POST">
                <div class="tab">
                    @csrf
                    <div class="form-group">
                        <label for="company">Nama Perusahaan</label>
                        <select name="company_id" id="company_id" class="form-control" value="{{ old('company_id') }}">
                            <option value="" selected disabled>Pilih Nama Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="company">Nama Proyek</label>
                        <select name="project_id" id="project_id" class="form-control" value="{{ old('project_id') }}">
                            <option value="" selected disabled>Pilih Nama Proyek</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="company">Nama Asesi</label>
                        <select name="project_participant_id" id="participant_id" class="form-control"
                            value="{{ old('participant_id') }}">
                            <option value="" selected disabled>Pilih Nama Asesi</option>
                        </select>
                    </div>

                    <div class="form-group" id="type-doc">
                        <label for="type">Ekstensi</label>
                        <select name="type" class="form-control">
                            <option value="pdf">PDF</option>
                            <option value="docx" selected>DOCX</option>
                        </select>
                    </div>

                    <div class="chart" id="summary_chart"></div>
                    <div class="chart" id="competency_chart"></div>
                    <div class="chart" id="key_behavior_chart"></div>
                </div>
                <div class="tab">
                    <div class="form-group">
                        <label for="">Hasil</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Kompetensi</th>
                                    <th class="text-center" style="width:10%">Gap</th>
                                    <th class="text-center" style="width:10%">LoA</th>
                                </tr>
                            </thead>

                            <tbody id="result-data">

                            </tbody>
                        </table>

                    </div>

                    <div class="form-group">
                        <label for="">Summary</label>
                        <textarea name="summary" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="">Saran</label>
                        <textarea name="suggestion" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div style="overflow:auto;">
                    <div style="float:right;">
                        <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                        <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
                    </div>
                </div>

                <div style="text-align:center;margin-top:40px;">
                    <span class="step"></span>
                    <span class="step"></span>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
    @if (Session::has('download.in.the.next.request'))
        <meta http-equiv="refresh" content="5;url={{ Session::get('download.in.the.next.request') }}">
    @endif

    <style>
        .chart {
            display: none;
        }

        #regForm {
            background-color: #ffffff;
            padding: 40px;
        }

        input {
            padding: 10px;
            width: 100%;
            font-size: 17px;
            border: 1px solid #aaaaaa;
        }

        input.invalid {
            background-color: #ffdddd;
        }

        .tab {
            display: none;
        }

        button {
            background-color: #4CAF50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 17px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.8;
        }

        #prevBtn {
            background-color: #bbbbbb;
        }

        .step {
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbbbbb;
            border: none;
            border-radius: 50%;
            display: inline-block;
            opacity: 0.5;
        }

        .step.active {
            opacity: 1;
        }

        .step.finish {
            background-color: #4CAF50;
        }
    </style>
@endsection

@section('js')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
        $('#type-doc').hide();
        $(document).ready(function() {
            $('#company_id').change(function() {
                $.get("{{ url('data/assessment-project-data-by-company/') }}" + "/" + $("#company_id")
                    .val(),
                    function(data) {
                        $('#project_id').empty();
                        $('#project_id').append(
                            `<option value="" selected disabled>Pilih Nama Proyek</option>`);
                        data.forEach(project => {
                            $('#project_id').append(
                                `<option value="${project.id}">${project.name}</option>`);
                        });
                    });
            });

            $('#project_id').change(function() {
                $.get("{{ url('data/assessment-participant-data-by-project/') }}" + "/" + $("#project_id")
                    .val(),
                    function(data) {
                        $('#participant_id').empty();
                        $('#participant_id').append(
                            `<option value="" selected disabled>Pilih Nama Asesi</option>`);
                        data.forEach(participant => {
                            $('#participant_id').append(
                                `<option value="${participant.id}">${participant.name}</option>`
                            );
                        });
                    });
            });
        });
    </script>

    <script>
        var summaryChart;
        var competencyCharts = []
        var keyBehaviorCharts = [];
        var color = ["#f44336", "#e91e63", "#8a4af3", "#673ab7", "#3f51b5"];
        var currentTab = 0;
        showTab(currentTab);

        function showTab(n) {
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "inline";
            }
            if (n == (x.length - 1)) {
                document.getElementById("nextBtn").innerHTML = "Submit";
            } else {
                document.getElementById("nextBtn").innerHTML = "Next";
            }
            fixStepIndicator(n)
        }

        function nextPrev(n) {
            var x = document.getElementsByClassName("tab");
            if (n == 1 && !validateForm()) return false;
            x[currentTab].style.display = "none";
            currentTab = currentTab + n;
            if (currentTab == 1) {
                $.post("{{ url('assessment/report-assessment-data') }}", $('#report-assessment-form').serialize())
                    .done(function(data) {
                        $('#result-data').empty();
                        data.forEach(result => {
                            $('#result-data').append(
                                `<tr>
                    <td>${result.name}</td>
                    <td>${result.gap.toFixed(2)}</td>
                    <td>${result.loa.toFixed(2) ?? '-'}</td>
                </tr>`
                            )
                        });
                    });
            } else if (currentTab >= x.length) {
                document.getElementById("report-assessment-form").submit();
            }
            showTab(currentTab);
        }

        function validateForm() {
            var x, y, i, valid = true;
            y = $(`.tab:eq(${currentTab}) :text`);
            z = $(`.tab:eq(${currentTab}) textarea`);

            for (i = 0; i < y.length; i++) {
                if (y[i].value == "") {
                    y[i].className += " invalid";
                    valid = false;
                }
            }

            for (i = 0; i < z.length; i++) {
                if (z[i].value == "") {
                    z[i].className += " invalid";
                    valid = false;
                }
            }

            if (valid) {
                document.getElementsByClassName("step")[currentTab].className += " finish";
            }
            return valid;
        }

        function fixStepIndicator(n) {
            var i, x = document.getElementsByClassName("step");
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(" active", "");
            }
            x[n].className += " active";
        }
    </script>

@endsection
