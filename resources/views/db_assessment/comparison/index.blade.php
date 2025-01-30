@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Komparasi Hasil Assessment</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form" action="/db-assessment/comparison-show-participants" method="GET" id="comparison-form">
                @csrf
                <div class="row">
                    <div class="col">
                        <h4>Perusahaan A</h4>
                        <div class="form-group">
                            <label for="">Nama Perusahaan</label>
                            <select name="first_company_id" id="first_company_id" class="form-control" value="{{old('first_company_id')}}" required>
                                <option value="" selected disabled>Pilih Nama Perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{$company->id}}">{{$company->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <h4>Perusahaan B</h4>
                        <div class="form-group">
                            <label for="">Nama Perusahaan</label>
                            <select name="second_company_id" id="second_company_id" class="form-control" value="{{old('second_company_id')}}" required>
                                <option value="" selected disabled>Pilih Nama Perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{$company->id}}">{{$company->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Target Job</label>
                    <select name="target_job_id" id="target_job_id" class="form-control" value="{{old('target_job')}}" required>
                        <option value="" selected disabled>Pilih Target Job</option>
                    </select>
                </div>
                <div class="form-group" id="penamaanRekomendasi1">
                    <label for="recommendation_type">Penamaan Rekomendasi</label>
                    <select class="form-control @error('recommendation_type') is-invalid @enderror" name="recommendation_type" id="recommendation_type" required>
                        <option value="" disabled selected>== Pilih Penamaan Rekomendasi ==</option>
                    </select>
                    @error('recommendation_type')
                      <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="">Tanggal Terakhir</label>
                    <input type="date" class="form-control" name="end_date" required>
                </div>
                <div class="text-right">
                    <button type="submit" id="send" class="btn btn-primary">Selanjutnya</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $("#first_company_id, #second_company_id").on("change", function(){
                if($("#first_company_id").val() != null && $("#second_company_id").val() != null){
                    $.get("{{url('data/dbassessment-target-job-by-company')}}",
                        { first_company_id: $("#first_company_id").val(), second_company_id: $("#second_company_id").val() } )
                    .done(function( data ) {
                        $('#target_job_id').empty();
                        $('#target_job_id').append(`<option value="" disabled selected>== Pilih Target Job ==</option>`)
                        data.forEach(targetJob => {
                            $('#target_job_id').append(`<option value="${targetJob.id}">${targetJob.name}</option>`);
                        });
                    });

                    $.get("{{url('data/dbassessment-recommendation-type-by-company')}}",
                        { first_company_id: $("#first_company_id").val(), second_company_id: $("#second_company_id").val() } )
                    .done(function( data ) {
                        $('#recommendation_type').empty();
                        $('#recommendation_type').append(`<option value="" disabled selected>== Pilih Penamaan Rekomendasi ==</option>`)
                        data.forEach(recommendationType => {
                            $('#recommendation_type').append(`<option value="${recommendationType.id}">${recommendationType.name}</option>`);
                        });
                    });
                }
            })
        })
    </script>
@endsection
