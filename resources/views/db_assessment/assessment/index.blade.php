@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 text-dark">Nilai Hasil Assessment</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="/db-assessment/assessments" method="POST" id="regForm">
            @csrf
            <div class="tab">
                <div class="form-group">
                    <label for="name">Nama Project</label>
                    <input type="text" class="form-control input-name @error('name') is-invalid @enderror" id="name"
                        placeholder="Nama Project" name="name" value="{{old('name')}}">
                    @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="company">Nama Perusahaan</label>
                    <select class="form-control input-name @error('company') is-invalid @enderror" id="company"
                        name="company">
                        <option value="0" disabled>== Pilih Perusahaan ==</option>
                        @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company')
                    <div class="alert alert-danger">Perusahaan belum di pilih</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="job">Target Job</label>
                    <select class="form-control input-name @error('job') is-invalid @enderror" id="job" name="job">
                        <option value="0" disabled>== Pilih Target Job ==</option>
                        @foreach ($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->name }}</option>
                        @endforeach
                    </select>
                    @error('job')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" class="form-control input-name @error('start_date') is-invalid @enderror"
                        id="start_date" name="start_date" value="{{old('start_date')}}">
                    @error('start_date')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_date">Tanggal Akhir</label>
                    <input type="date" class="form-control input-name @error('last_date') is-invalid @enderror"
                        id="last_date" name="last_date" value="{{old('last_date')}}">
                    @error('last_date')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="recomendasi">Nama Rekomendasi</label>
                    <select class="form-control input-name @error('recomendasi') is-invalid @enderror" id="recomendasi"
                        placeholder="Nama Kompetensi" name="recomendasi">
                        <option value="0" selected disabled>== Pilih Rekomendasi ==</option>
                        @foreach($recommendations as $recommendation)
                        <option value="{{ $recommendation->id }}">
                            {{ $recommendation->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('recomendasi')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="table-responsive">
                    <table class="table" style="width: 100%" border="1" class="competencies" id="competencies">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 2%">
                                    No
                                </th>
                                <th style="width: 45%">Nama Kompetensi</th>
                                <th style="width: 45%">Standar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="trStatic">
                                <th colspan="3" style="text-align: center">Tidak ada data kompetensi</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab">
                <div class="alert alert-danger alert-dismissible fade show" id="alert" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <strong>Belum ada asesi yang dipilih</strong>
                </div>
                <div class="form-group">
                    <label for="company">Nama Perusahaan</label>
                    <input type="text" class="form-control" id="name_company" readonly name="name_company" value="">
                </div>
                <div class="table-responsive">
                    <table class="table" border="1" style="width: 100%" id="step2">
                        <thead class="thead-dark">
                            <tr class="trStaticStep2">
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input test" type="checkbox" value="" id="checkAll-step2">
                                        </label>
                                        <label class="form-check-label" for="defaultCheck1">
                                    </div>
                                </th>
                                <th>Nama Asesi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="overflow:auto;">
                <div style="float:right;">
                    <button type="button" id="prevBtn" class="btn btn-primary" onclick="nextPrev(-1)">Previous</button>
                    <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextPrev(1)">Next</button>
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
<style>
    input {
        padding: 10px;
        width: 100%;
        font-size: 17px;
        font-family: Raleway;
        border: 1px solid #aaaaaa;
    }

    input.invalid {
        background-color: #ffdddd;
    }

    .tab {
        display: none;
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
    #alert{
        display: none;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        $('select#company option[value="0"]').attr("selected", true);
        $('select#job option[value="0"]').attr("selected", true);
        $('select#recomendasi option[value="0"]').attr("selected", true);
        $('input#checkAll').prop('checked', false)

        $('#checkAll').click(function () {
            var check = document.getElementsByClassName('checkDynamic')
            var count = check.length
            if (this.checked) {
                for (let index = 1; index <= count; index++) {
                    document.getElementById(index).checked = true
                    document.getElementById('input' + index).removeAttribute('disabled')
                    document.getElementById('id' + index).removeAttribute('disabled')
                    document.getElementById('name' + index).removeAttribute('disabled')
                    document.getElementById('competence_id' + index).removeAttribute('disabled')
                }
            } else {
                for (let index = 1; index <= count; index++) {
                    document.getElementById(index).checked = false
                    document.getElementById('input' + index).setAttribute('disabled', 'disabled')
                    document.getElementById('id' + index).setAttribute('disabled', 'disabled')
                    document.getElementById('name' + index).setAttribute('disabled', 'disabled')
                    document.getElementById('competence_id' + index).setAttribute('disabled',
                        'disabled')
                }
            }
        });

        $('select#job').change(function () {
            $('.trStatic').remove()
            $('.trDynamic').remove()
            var id = $(this).val()
            var counter = 0
            var row = $('<tr>')
            $.ajax({
                type: 'GET',
                url: '/data/dbassessment-competence-data',
                data: {
                    'job_id': id
                },
                success: function (response) {
                    $.each(response, function (key, val) {
                        counter += 1
                        var tr = $('<tr>').append(
                            $('<td>').html(counter),
                            $('<td>').html("<input type='hidden' id='id" +
                                counter + "'  name='id[]' value=" + val
                                .competency.id + ">" +
                                "<input type='hidden'  id='name" +
                                counter + "' name='competence[]' value=" + val
                                .competency.name + ">" +
                                "<input type='hidden'  id='competence_id" +
                                counter + "' name='competence_id[]' value=" +
                                val.competency.id + ">" +
                                val.competency.name),
                            $('<td>').html($(
                                '<div class="form-group" style="margin-bottom: 0px !important">' +
                                '<input   type="number" class="form-control input-name @error('
                                standar ') is-invalid @enderror" id="input' +
                                counter +
                                '" placeholder="Masukan Standar" name="standar[]" value="{{old('
                                standar ')}}">' +
                                '@error('
                                standar ')' +
                                '<div class="alert alert-danger">'
                                '{{ $message }}</div>' +
                                '@enderror </div>'))
                        ).addClass('trDynamic');
                        $('table#competencies').append(tr)
                    })
                },
                dataType: 'json'
            });
        });
        $(document).on('click', '.checkDynamic', function () {
            var id = $(this).attr('id')
            if (this.checked) {
                document.getElementById('input' + id).removeAttribute('disabled')
                document.getElementById('id' + id).removeAttribute('disabled')
                document.getElementById('name' + id).removeAttribute('disabled')
                document.getElementById('competence_id' + id).removeAttribute('disabled')
            } else {
                document.getElementById('input' + id).setAttribute('disabled', 'disabled')
                document.getElementById('id' + id).setAttribute('disabled', 'disabled')
                document.getElementById('name' + id).setAttribute('disabled', 'disabled')
                document.getElementById('competence_id' + id).setAttribute('disabled', 'disabled')
            }
        })
    });
</script>


<script>
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
        if (currentTab >= x.length) {
            if($('.checkDynamicStep2:checked').length > 0){
                document.getElementById("regForm").submit();
            }else {
                $('#alert').show()
                currentTab = 1
            }
        }
        if (n == 1 && $('#alert').css("display") == "none") {
            var id = document.getElementById('company').value
            var standar = document.getElementsByName('standar[]')
            var competence_id = document.getElementsByName('competence_id[]')
            var recomendasi_value = document.getElementById('recomendasi').value
            var competencies = []
            var recomendasi = []

            for (let index = 1; index <= standar.length; index++) {
                if (document.getElementById('name' + index).disabled == false) {
                    var competence = document.getElementById('name' + index).value
                    competencies.push(competence)
                    $('.trStaticStep2').append('<th class="thDynamicStep2">' + competence + '</th>')
                }
            }
            $('.trStaticStep2').append('<th class="thDynamicStep2">Rekomendasi</th>')

            $.ajax({
                type: 'GET',
                url: '/data/dbassessment-company-name',
                data: {
                    'id': id
                },
                dataType: 'JSON',
                success: function (response) {
                    document.getElementById('name_company').value = response.name

                }
            })

            $.ajax({
                type: 'GET',
                url: '/data/dbassessment-participants-data',
                data: {
                    'id': id,
                    'recomendasi_id': recomendasi_value
                },
                dataType: 'JSON',
                success: function (response) {
                    $.each(response.participant, function (key, value) {
                        var td1 = $('<td>').html($('<div class="form-check">' +
                            '<input class="form-check-input checkDynamicStep2" type="checkbox" id="' +
                            value.id + '">' +
                            '<label class="form-check-label" for="defaultCheck1">' +
                            '</dv>'))

                        var td2 = $('<td>').html(
                            "<input type='hidden' disabled id='participants-step2" + value.id +
                            "' class='participants-step2'  name='participants[]' value=" + value
                            .id + ">" +
                            "<input type='hidden' id='participants' name='' value=" + value
                            .name + ">" +
                            value.name)

                        var optionDynamic = []
                        $.each(response.recomendasi, function (key2, value2) {
                            optionDynamic.push('<option value="' + value2.id + '">' + value2
                                .name + '</option>')
                        })

                        var td3 = $('<td>').html(
                            '<select disabled style="width : 200px" class="form-control reommendations" id="recomendasi-step2' +
                            value.id + '" name="recommendations[]">' +
                            '<option value="0" disabled selected>== Pilih Rekomendasi ==</option>' +
                            optionDynamic +
                            '</select>')

                        var id = value.id
                        var tdArray = []
                        $.each(competence_id, function (key, value) {
                            if (value.value != "" && value.disabled === false) {
                                var tdDynamic = $('<td>').html(
                                    "<input class='form-control competence-id-step2 competence-id-step2" +
                                    id +
                                    "' disabled type='hidden' id='competency-id-step2" +
                                    id + "' name='competency_id[]' value='" + value
                                    .value + "'>" +
                                    "<input style='width : 150px' class='form-control competence-step2 competence-step2" +
                                    id + "' disabled type='text' id='competence-step2" +
                                    id +
                                    "' name='standars[]' placeholder ='Nilai'>"
                                    )
                                tdArray.push(tdDynamic)
                            }
                        })

                        var tr = $('<tr>').append(
                            td1, td2,
                            $.each(tdArray, function (key, value) {
                                $('<td>').html(value)
                            }), td3
                        ).addClass('tdDynamicStep2')
                        $('table#step2').append(tr)

                    });
                }
            });

        } else if (n == -1) {
            $('.thDynamicStep2').remove()
            $('.tdDynamicStep2').remove()
        }

        showTab(currentTab);
    }

    function validateForm() {
        var x, y, i, valid = true;

        x = document.getElementsByClassName("tab");
        y = x[currentTab].getElementsByClassName("input-name");
        for (i = 0; i < y.length; i++) {
            if (y[i].value == "" || y[i].value == 0) {
                y[i].className += " is-invalid";
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

    $(document).ready(function () {
        document.getElementById('checkAll-step2').checked = false
        $('#checkAll-step2').click(function () {
            var check = document.getElementsByClassName('checkDynamicStep2')
            var count = check.length
            if (this.checked) {
                var check = document.getElementsByClassName('checkDynamicStep2')
                var input_participant = document.getElementsByClassName('participants-step2')
                var input_competence = document.getElementsByClassName('competence-step2')
                var competence_id = document.getElementsByClassName('competence-id-step2')
                var reommendations = document.getElementsByClassName('reommendations')

                for (let index = 0; index < check.length; index++) {
                    check[index].checked = true
                    input_participant[index].removeAttribute('disabled')
                }

                for (let index = 0; index < input_competence.length; index++) {
                    input_competence[index].removeAttribute('disabled')
                }

                for (let index = 0; index < competence_id.length; index++) {
                    competence_id[index].removeAttribute('disabled')
                }

                for (let index = 0; index < reommendations.length; index++) {
                    reommendations[index].removeAttribute('disabled')
                }
            } else {
                var check = document.getElementsByClassName('checkDynamicStep2')
                var input_participant = document.getElementsByClassName('participants-step2')
                var input_competence = document.getElementsByClassName('competence-step2')
                var competence_id = document.getElementsByClassName('competence-id-step2')
                var reommendations = document.getElementsByClassName('reommendations')

                for (let index = 0; index < check.length; index++) {
                    check[index].checked = false
                    input_participant[index].setAttribute('disabled', 'disabled')
                }

                for (let index = 0; index < input_competence.length; index++) {
                    input_competence[index].setAttribute('disabled', 'disabled')
                }

                for (let index = 0; index < competence_id.length; index++) {
                    competence_id[index].setAttribute('disabled', 'disabled')
                }

                for (let index = 0; index < reommendations.length; index++) {
                    reommendations[index].setAttribute('disabled', 'disabled')
                }
            }
        });

        $(document).on('click', '.checkDynamicStep2', function () {
            var id = $(this).attr('id')
            if (this.checked) {
                document.getElementById('participants-step2' + id).removeAttribute('disabled')
                document.getElementById('recomendasi-step2' + id).removeAttribute('disabled')
                document.getElementById('recomendasi-step2' + id).classList.add('input-name')
                var competence_id = document.getElementsByClassName('competence-id-step2' + id)
                var competence = document.getElementsByClassName('competence-step2' + id)
                for (let index = 0; index < competence_id.length; index++) {
                    competence_id[index].removeAttribute('disabled')
                    competence[index].removeAttribute('disabled')
                    competence_id[index].classList.add('input-name')
                    competence[index].classList.add('input-name')
                }
            } else {
                document.getElementById('participants-step2' + id).setAttribute('disabled', 'disabled')
                document.getElementById('recomendasi-step2' + id).setAttribute('disabled', 'disabled')
                document.getElementById('recomendasi-step2' + id).classList.remove('input-name')
                var competence_id = document.getElementsByClassName('competence-id-step2' + id)
                var competence = document.getElementsByClassName('competence-step2' + id)
                for (let index = 0; index < competence_id.length; index++) {
                    competence_id[index].setAttribute('disabled', 'disabled')
                    competence[index].setAttribute('disabled', 'disabled')
                    competence_id[index].classList.remove('input-name')
                    competence[index].classList.remove('input-name')
                }
            }
        });

    })
</script>
@endsection
