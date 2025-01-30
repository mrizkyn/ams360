@extends('adminlte::page')

@section('title', 'Analisa Hasil Assessment Per Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Analisa Per Perusahaan</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <form action="/db-assessment/recap-show-participants" method="GET">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="company">Nama Perusahaan</label>
                        <select class="form-control @error('company') is-invalid @enderror" name="company" id="company">
                            <option value="0" disabled selected>== Pilih Perusahaan ==</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                          @endforeach
                        </select>
                        @error('company')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" id="targetJob1">
                        <label for="targetJob">Target Job</label>
                        <select class="form-control @error('targetJob') is-invalid @enderror" name="targetJob" id="targetJob">
                          <option value="0" disabled selected>== Pilih Target Job ==</option>
                        </select>
                        @error('targetJob')
                          <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" id="startDate1">
                      <label for="startDate">Tanggal Mulai</label>
                      <input type="date" class="form-control @error('startDate') is-invalid @enderror" id="startDate" name="startDate" value="{{ old('startDate') }}">
                      @error('startDate')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="form-group" id="endDate1">
                      <label for="endDate">Tanggal Terakhir</label>
                      <input type="date" class="form-control @error('endDate') is-invalid @enderror" id="endDate" name="endDate" value="{{ old('endDate') }}">
                      @error('endDate')
                        <div class="alert alert-danger">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="form-group">
                        <label for="reportType">Jenis Laporan</label>
                        <select class="form-control @error('reportType') is-invalid @enderror" name="reportType" id="reportType">
                          <option value="0" disabled selected>== Pilih Jenis Laporan ==</option>
                          <option value="1">Rekap Hasil Assessment</option>
                          <option value="2">Statistik Sebaran Peserta Per Rekomendasi</option>
                          <option value="3">Statistik Gap Kompetensi</option>
                          <option value="4">Perbandingan Kompetensi</option>
                        </select>
                        @error('reportType')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" id="penamaanRekomendasi1">
                        <label for="recommendationType">Penamaan Rekomendasi</label>
                        <select class="form-control @error('recommendationType') is-invalid @enderror" name="recommendationType" id="recommendationType">
                          <option value="0" disabled selected>== Pilih Penamaan Rekomendasi ==</option>
                        </select>
                        @error('recommendationType')
                          <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                  <div class="form-group text-right">
                    <input type="submit" name="submit" id="submit" value="Selanjutnya" class="btn btn-info">
                  </div>
                </div>
              </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $('#penamaanRekomendasi1').hide();
        var rekap;
        $("#reportType").change(function () {
          if ($('#reportType option:selected').val() == "2" || $('#reportType option:selected').val() == "4") {
            $('#penamaanRekomendasi1').show();
          } else {
            $('#penamaanRekomendasi1').hide();
          }
        });

        $("#company").change(function () {
          $.get("{{url('db-assessment/company-target-jobs')}}"+ "/" + $("#company option:selected").val(), function(data){
            $('#targetJob').empty();
            $('#targetJob').append(`<option value="0" disabled selected>== Pilih Target Job ==</option>`)
            data.forEach(targetJob => {
                $('#targetJob').append(`<option value="${targetJob.id}">${targetJob.name}</option>`);
            });
          });

          $.get("{{url('db-assessment/company-recommendation-types')}}"+ "/" + $("#company option:selected").val(), function(data){
            $('#recommendationType').empty();
            $('#recommendationType').append(`<option value="0" disabled selected>== Pilih Penamaan Rekomendasi ==</option>`)
            data.forEach(recommendationType => {
                $('#recommendationType').append(`<option value="${recommendationType.id}">${recommendationType.name}</option>`);
            });
          });
        });


      });
    </script>
@endsection
