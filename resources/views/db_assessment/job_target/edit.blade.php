@extends('adminlte::page')

@section('title', 'Target Job')

@section('content_header')
    <h1 class="m-0 text-dark">Target Job</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                @if (session('kompetensi'))
                    <div class="alert alert-danger">
                        <ul>
                            <li>{{ session('kompetensi') }}</li>
                        </ul>
                    </div>
                @endif
                <form action="/db-assessment/targetJobs/{{ $targetJob->id }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="namaTargetJob">Nama Target Job</label>
                            <input id="namaTargetJob" type="text" class="form-control @error('namaTargetJob') is-invalid @enderror" placeholder="Masukkan nama target job" name="namaTargetJob" autofocus value="{{ $targetJob->name }}">
                            <small>*Note : Untuk Target Job yang memiliki nama yang sama namun dengan Kompetensi yang berbeda, Penamaan disesuaikan oleh perusahaan</small>
                            @error('namaTargetJob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <label for="competency-table">Kompetensi</label>
                        <table class="table table-bordered" id="competency-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 5%; text-align: center;"><input type="checkbox" id="select-all-competency"></th>
                                    <th style="width: 95%">Nama Kompetensi</th>
                                </tr>
                            </thead>
                            <tbody id="competency-table-data">
                                @foreach ($competencies as $competency)
                                    <tr>
                                        <td style="text-align: center;"><input type="checkbox" name="selected_competencies[]" value="{{$competency->id}}" {{ in_array($competency->id, $targetJobCompetencies) ? 'checked' : '' }} ></th>
                                        <td>{{$competency->name}}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer">
                        <div class="form-group text-right">
                            <a href="/db-assessment/targetJobs" class="btn btn-default">Kembali</a>
                            <input type="submit" name="submit" value="Ubah" class="btn btn-info">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $("#select-all-competency").click(function(e){
                var table = $(e.target).closest('#competency-table');
                $('td input:checkbox',table).prop('checked',this.checked).change();
            });
        });
    </script>
@stop