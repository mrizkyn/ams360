@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 text-dark">Perpustakaan Kompetensi</h1>
@stop


@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="report">
                    @csrf
                    <div class="form-group">
                        <label for="competence">Kompetensi</label>
                        <select name="competence" id="competence"
                        class="form-control @error('competence') is-invalid @enderror">
                        <option value="" selected disabled>== Pilih Kompetensi ==</option>
                        <option value="company" {{ old('competence') == "company" ? 'selected' : '' }}>Perperusahaan</option>
                        <option value="all" {{ old('competence') == "all" ? 'selected' : '' }}>Semua Kompetensi</option>
                    </select>
                    @error('competence')
                    <div class="alert alert-danger" style="margin-top: 10px">Kompetensi belum di pilih</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="company">Perusahaan</label>
                    <select name="company" id="company" class="form-control @error('company') is-invalid @enderror">
                        <option value="0" disabled>== Pilih Perusahaan ==</option>
                        @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company')
                    <div class="alert alert-danger" style="margin-top: 10px">Perusahaan belum di pilih</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="division">Divisi</label>
                    <select name="division" id="division"
                    class="form-control @error('division') is-invalid @enderror">
                    <option value="0" disabled>== Pilih Divisi ==</option>
                            {{-- @foreach ($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach --}}
                        </select>
                        @error('division')
                        <div class="alert alert-danger" style="margin-top: 10px">Divisi belum di pilih</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="departement">Departemen</label>
                        <select name="departement" id="departement"
                        class="form-control @error('departement') is-invalid @enderror">
                        <option value="0" disabled>== Pilih Departemen ==</option>
                            {{-- @foreach ($departements as $departement)
                            <option value="{{ $departement->id }}">{{ $departement->name }}</option>
                            @endforeach --}}
                        </select>
                        @error('departement')
                        <div class="alert alert-danger" style="margin-top: 10px">Departemen belum di pilih</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="position">Jabatan</label>
                        <select name="position" id="position"
                        class="form-control @error('position') is-invalid @enderror">
                        <option value="0" disabled>== Pilih Jabatan ==</option>
                            {{-- @foreach ($positions as $position)
                            <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach --}}
                        </select>
                        @error('position')
                        <div class="alert alert-danger" style="margin-top: 10px">Departemen belum di pilih</div>
                        @enderror
                    </div>
                    <div style="text-align: right">
                        <button id="doc" type="button" class="btn btn-primary"><i class="fas fa-file-word"></i> Cetak
                        Perpustakaan Kompetensi</button>
                        {{-- <button id="pdf" type="button" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Cetak
                        PDF</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Konfirmasi Hapus</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            Apakah anda yakin ingin menghapus?
        </div>
        <div class="modal-footer">
            <form action="" method="POST" id="myForm">
                @csrf
                @method('DELETE')
                <button type="button" type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                <button type="submit" type="button" class="btn btn-primary">Ya</button>
            </form>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="alert-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Permberitahuan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            Anda Harus memilih terlebih dahulu <label for="">Kompetensi</label>!
        </div>
        <div class="modal-footer">
            <button type="button" type="button" class="btn btn-secondary" data-dismiss="modal">Ya</button>
        </div>
    </div>
</div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css" />
@stop

@section('js')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('select#competence option[value="0"]').attr("selected", true);
        $('select#company option[value="0"]').attr("selected", true);
        $('select#company').attr("disabled", "disabled");
        $('select#division option[value="0"]').attr("selected", true);
        $('select#division').attr("disabled", "disabled");
        $('select#departement option[value="0"]').attr("selected", true);
        $('select#departement').attr("disabled", "disabled");
        $('select#position option[value="0"]').attr("selected", true);
        $('select#position').attr("disabled", "disabled");

        $('button#doc').click(function () {
            if ($('select#competence option:selected').val() == "all") {
                var valueCompetence = $('select#competence option:selected').val()
                if (valueCompetence == '0') {
                    $('#alert-modal').modal('show')
                } else {
                    $('form#report').attr('method', 'GET')
                    $('form#report').attr('action', '/data/assessment-competence-doc')
                    $('form#report').submit()
                }
            } else if ($('select#competence option:selected').val() == "company") {
                console.log($('select#division option:selected').val());

                if ($('select#company option:selected').val() == "0" || $('select#company option:selected').val() == "" || $('select#company option:selected').disabled == true || $('select#division option:selected').val() == "" || $('select#division option:selected').val() == "0" || $('select#division option:selected').disabled == true || $('select#departement option:selected').val() == "" || $('select#departement option:selected').val() == "0" || $('select#departement option:selected').disabled == true || $('select#position option:selected').val() == "" || $('select#position option:selected').val() == "0" || $('select#position option:selected').disabled == true) {

                    alert('Harap pilih Perusahaan, Divisi, Departemen, Jabatan')
                } else {
                    $('form#report').attr('method', 'GET')
                    $('form#report').attr('action', '/data/assessment-competence-doc')
                    $('form#report').submit()
                }
            }
        });

        $('button#pdf').click(function () {
            var valueCompetence = $('select#competence option:selected').val()
            if (valueCompetence == '0') {
                $('#alert-modal').modal('show')
            } else {
                $('form#report').attr('method', 'GET')
                $('form#report').attr('action', '/data/assessment-competence-pdf')
                $('form#report').submit()
            }
        });

        $('select#competence').change(function () {
            if ($(this).val() == 'all') {
                $('select#company').val('0').trigger('change');
                $('select#division').val('0').trigger('change');
                $('select#departement').val('0').trigger('change');

                $('select#company').attr("disabled", "disabled");
                $('select#division').attr("disabled", "disabled");
                $('select#departement').attr("disabled", "disabled");
                $('select#position').attr("disabled", "disabled");
            } else {
                $('select#company').removeAttr('disabled');
                // $('select#departement').removeAttr('disabled');
                // $('select#division').removeAttr('disabled');
                // $('select#position').removeAttr('disabled');
            }
        });

        $('select#company').change(function () {
            var company_id = $(this).val()
            var select_division = document.getElementById('division')
            $('select#division').val('0').trigger('change');
            $('select#departement').val('0').trigger('change');
            $('select#position').val('0').trigger('change');

            $('select#division').attr("disabled", "disabled");
            $('select#departement').attr("disabled", "disabled");
            $('select#position').attr("disabled", "disabled");
            $.ajax({
                url: '/data/assessment-report-division',
                data: {
                    company_id: company_id
                },
                success: function (result) {
                    if (result.status == 0) {
                        $('select#division').empty()
                        $('<option>').val(null).text('== Pilih Divisi ==').appendTo(
                            'select#division')
                        $('<option>').val(null).text(result.message).appendTo(
                            'select#division')
                    } else {
                        $('select#division').removeAttr('disabled');
                        $('select#division').empty()
                        $('<option>').val(null).text('== Pilih Divisi ==').appendTo(
                            'select#division')
                        result.forEach(element => {
                            // console.log(element);
                            $('<option>').val(element.division.id).text(element
                                .division.name).appendTo('select#division')
                        });
                    }
                }
            })
        })

        $('select#division').change(function () {
            var company_id = $('select#company').val()
            var division_id = $(this).val()
            $('select#departement').val('0').trigger('change');
            $('select#position').val('0').trigger('change');

            $('select#departement').attr("disabled", "disabled");
            $('select#position').attr("disabled", "disabled");
            $.ajax({
                url: '/data/assessment-report-departement',
                data: {
                    company_id: company_id,
                    division_id: division_id
                },
                success: function (result) {
                    if (result.status == 0) {
                        // console.log(result);
                        $('select#departement').empty()
                        $('<option>').val(null).text('== Pilih Departemen ==').appendTo(
                            'select#departement')
                        $('<option>').val(null).text(result.message).appendTo(
                            'select#departement')
                    } else {
                        // console.log(result);
                        $('select#departement').removeAttr('disabled');
                        $('select#departement').empty()
                        $('<option>').val(null).text('== Pilih Departemen ==').appendTo(
                            'select#departement')
                        result.forEach(element => {
                            // console.log(element);
                            $('<option>').val(element.departement.id).text(element
                                .departement.name).appendTo(
                                'select#departement')
                            });
                    }
                }
            })
        })

        $('select#departement').change(function () {
            var company_id = $('select#company').val()
            var division_id = $('select#division').val()
            var departement_id = $(this).val()
            $('select#position').val('0').trigger('change');

            $('select#position').attr("disabled", "disabled");
            $.ajax({
                url: '/data/assessment-report-position',
                data: {
                    company_id: company_id,
                    division_id: division_id,
                    departement_id: departement_id
                },
                success: function (result) {
                    if (result.status == 0) {
                        // console.log(result);
                        $('select#position').empty()
                        $('<option>').val(null).text('== Pilih Jabatan ==').appendTo(
                            'select#position')
                        $('<option>').val(null).text(result.message).appendTo(
                            'select#position')
                    } else {
                        // console.log(result);
                        $('select#position').removeAttr('disabled');
                        $('select#position').empty()
                        $('<option>').val(null).text('== Pilih Jabatan ==').appendTo(
                            'select#position')
                        result.forEach(element => {
                            // console.log(element);
                            $('<option>').val(element.position_id).text(element
                                .position).appendTo('select#position')
                        });
                    }
                }
            })
        })

        if ($('select#competence').val() !== null) {
            $('select#company').removeAttr('disabled');
        }

    });
</script>
@endsection