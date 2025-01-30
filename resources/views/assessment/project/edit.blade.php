@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Buat Proyek</h1>
@stop
@section('css')
<style>
    .fixed-header{
        table-layout: fixed;
        border-collapse: collapse;
    }

    .fixed-header tbody{
        display: block;
        width: 100%;
        overflow: auto;
        height: 320px;
    }

    .fixed-header thead tr{
        display: table;
        width: 100%;
    }
</style>
@endsection

@section('content')
    <form action="{{url('assessment/projects').'/'.$project->id}}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header">
                <b>Identitas Proyek</b>
            </div>
            <div class="card-body">
                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                    <label for="name">Nama Proyek</label>
                    <input type="text" name="name" class="form-control" value="{{$project->name}}" placeholder="Masukan Nama Proyek" required>
                </div>
                <div class="form-group {{ $errors->has('company_id') ? ' has-error' : '' }}">
                    <label for="name">Nama Perusahaan</label>
                    <select name="company_id" id="company_id" class="form-control" value="{{old('company_id')}}" required>
                        <option value="" disabled>Pilih Nama Perusahaan</option>
                        @foreach ($companies as $company)
                            <option value="{{$company->id}}"
                                @if ($company->id == $project->company_id)
                                    selected
                                @endif
                            >
                                {{$company->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group {{ $errors->has('position_id') ? ' has-error' : '' }}">
                    <label for="name">Sasaran Jabatan</label>
                    <select name="position_id" id="position_id" class="form-control" value="{{old('position_id')}}" required>
                        <option value="" disabled selected>Pilih Target Jabatan</option>
                        @foreach ($positions as $position)
                            <option value="{{$position->id}}"
                                @if ($position->id == $project->position_id)
                                    selected
                                @endif
                            >
                                {{$position->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Waktu Awal</label>
                    <input type="date" class="form-control" name="start_date" placeholder="Masukan Tanggal Awal" value="{{$project->start_date}}" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Waktu Akhir</label>
                    <input type="date" class="form-control" name="end_date" placeholder="Masukan Tanggal Akhir" value="{{$project->end_date}}" required>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <b>Asesi Proyek</b>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="participant-table">
                        <thead class="thead-dark">
                            <tr>
                                <th><input type="checkbox" id="select-all-participant"></th>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Jabatan</th>
                                <th>Divisi</th>
                                <th>Departemen</th>
                                <th>Atasan</th>
                                <th>Rekan Kerja</th>
                                <th>Bawahan</th>
                            </tr>
                        </thead>
                        <tbody id="participant-table-data">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <b>Setup Kuesioner</b>
            </div>
            <div class="card-body">
                <input name="scale" value="10" type="hidden">

                <div class="form-group">
                    <label for="type">Tipe</label>
                    <select name="type" class="form-control" required>
                        <option value="" disabled>Pilih Tipe Soal</option>
                        <option value="1" {{$project->type == 1 ? 'selected' : ''}}>Current Proficiency (CP) – Current Proficiency Required (CPR)</option>
                        <option value="2" {{$project->type == 2 ? 'selected' : ''}}>Current Proficiency (CP)  – Future Proficiency Required (FPR)</option>
                        <option value="3" {{$project->type == 3 ? 'selected' : ''}}>Current Frequency (CF) – Current Frequency Required (CFR)</option>
                        <option value="4" {{$project->type == 4 ? 'selected' : ''}}>Current Frequency (CF) – Future Frequency Required (FFR)</option>
                    </select>
                </div>

                <table class="table table-bordered fixed-header" id="key-behavior-table">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 1%"><input type="checkbox" id="select-all-key-behavior"></th>
                            <th style="width: 32%">Kompetensi</th>
                            <th>Perilaku Kunci</th>
                        </tr>
                    </thead>
                    <tbody id="key-behavior-table-data">
                        @foreach ($keyBehaviors as $keyBehavior)
                            <tr>
                                <td style="width: 1%"><input type="checkbox" data-id="{{$keyBehavior->id}}"></th>
                                <td style="width: 32.6%">{{$keyBehavior->competence->name}}</td>
                                <td>{{$keyBehavior->description}}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" name="selected_participants" id="selectedParticipants">
        <input type="hidden" name="selected_key_behaviors" id="selectedKeyBehaviors">

        <div class="form-group text-right">
            <a href="\assessment\projects" class="btn btn-default">Kembali</a>
            <input class="btn btn-info" type="submit" value="Ubah" id="ubah">
        </div>
    </form>
@stop

@section('css')
    <style>
        .number{
            width: 100%;
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $("#company_id").change(function(){
                $("#participant-table-data").empty();
                $.get("{{url('data/assessment-project-participant-data')}}"+ "/" + $("#company_id").val(), function(data, status){
                    participants = data.data;
                    participants.forEach(participant => {
                        $("#participant-table-data").append(
                            `<tr>
                            <td><input type="checkbox" class="checkbox" data-id="${participant.id}"></th>
                            <td>${participant.identity_number}</th>
                            <td>${participant.name}</th>
                            <td>${participant.position}</th>
                            <td>${participant.division}</th>
                            <td>${participant.departement}</th>
                            <td><input type="number" maxlength="2" class="number superior_number" id="superior_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number collegue_number" id="collegue_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number subordinate_number" id="subordinate_number_${participant.id}" disabled></th>
                        </tr>`
                        )
                    });
                    $(document).on("change", "td input:checkbox" , function() {
                            $(this).prop('checked',this.checked);
                        });
                });
            });

            $("#participant-table-data").empty();
            $.get("{{url('data/assessment-project-participant-data')}}"+ "/" + $("#company_id").val(), function(data, status){
                participants = data.data;
                participants.forEach(participant => {
                    $("#participant-table-data").append(
                        `<tr>
                            <td><input type="checkbox" class="checkbox" data-id="${participant.id}"></th>
                            <td>${participant.identity_number}</th>
                            <td>${participant.name}</th>
                            <td>${participant.position}</th>
                            <td>${participant.division}</th>
                            <td>${participant.departement}</th>
                            <td><input type="number" maxlength="2" class="number superior_number" id="superior_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number collegue_number" id="collegue_number_${participant.id}" disabled></th>
                            <td><input type="number" maxlength="2" class="number subordinate_number" id="subordinate_number_${participant.id}" disabled></th>
                        </tr>`
                    )
                });
                $(document).on("change", "td input:checkbox" , function() {
                        $(this).prop('checked',this.checked);
                    });

                var projectParticipants = JSON.parse('{!! json_encode($projectParticipants) !!}');
                $("#participant-table tr").each(function(){
                    td = $('td', this);
                    projectParticipants.forEach(projectParticipant => {
                        checkbox = $('input:checkbox', td)
                        if(checkbox.attr('data-id') == projectParticipant.participant_id){
                            checkbox.prop('checked', true);
                            $('.superior_number', this).val(projectParticipant.superior_number);
                            $('.collegue_number', this).val(projectParticipant.collegue_number);
                            $('.subordinate_number', this).val(projectParticipant.subordinate_number);

                            $('.superior_number', this).removeAttr('disabled');
                            $('.collegue_number', this).removeAttr('disabled');
                            $('.subordinate_number', this).removeAttr('disabled');
                        }
                    });
                });
            });

            $(document).on('click' , '.checkbox' , function(){
                var id = $(this).attr('data-id')
                if(this.checked){
                    document.getElementById('superior_number_'+id).setAttribute('required' , 'required')
                    document.getElementById('collegue_number_'+id).setAttribute('required' , 'required')
                    document.getElementById('subordinate_number_'+id).setAttribute('required' , 'required')

                    document.getElementById('superior_number_'+id).removeAttribute('disabled')
                    document.getElementById('collegue_number_'+id).removeAttribute('disabled')
                    document.getElementById('subordinate_number_'+id).removeAttribute('disabled')
                }else{
                    document.getElementById('superior_number_'+id).setAttribute('disabled' , 'disabled')
                    document.getElementById('collegue_number_'+id).setAttribute('disabled' , 'disabled')
                    document.getElementById('subordinate_number_'+id).setAttribute('disabled' , 'disabled')

                    document.getElementById('superior_number_'+id).removeAttribute('required')
                    document.getElementById('collegue_number_'+id).removeAttribute('required')
                    document.getElementById('subordinate_number_'+id).removeAttribute('required')

                    $('#superior_number_'+id).val('')
                    $('#collegue_number_'+id).val('')
                    $('#subordinate_number_'+id).val('')
                }
            })

            var projectQuestions = JSON.parse('{!! json_encode($projectQuestions) !!}');
            $("#key-behavior-table td input:checkbox").each(function(){
                projectQuestions.forEach(projectQuestion => {
                    if($(this).attr('data-id') == projectQuestion.key_behavior_id){
                        $(this).prop('checked', true);
                    }
                });
            });


            $('#ubah').click(function(){
                var selectedParticipants = [];
                var selectedKeyBehaviors = [];
                $("#participant-table tr").each(function(){
                    td = $('td', this);
                    if($('input:checkbox', td).prop('checked')){
                        var participant = {
                            id: $('input:checkbox', td).attr("data-id"),
                            superior_number: $('.superior_number', this).val(),
                            collegue_number: $('.collegue_number', this).val(),
                            subordinate_number: $('.subordinate_number', this).val()
                        }
                        selectedParticipants.push(participant);
                    }
                })

                $("#key-behavior-table td input:checkbox").each(function(){
                    if($(this).prop('checked')){
                        selectedKeyBehaviors.push($(this).attr("data-id"));
                    }
                })

                $("#selectedParticipants").val(JSON.stringify(selectedParticipants));
                $("#selectedKeyBehaviors").val(JSON.stringify(selectedKeyBehaviors));
            });

            $("#select-all-participant").click(function(e){
                var table = $(e.target).closest('#participant-table');
                $('td input:checkbox',table).prop('checked',this.checked).change();
            });

            $("#select-all-key-behavior").click(function(e){
                var table = $(e.target).closest('#key-behavior-table');
                $('td input:checkbox',table).prop('checked',this.checked).change();
            });
        });
    </script>
@stop

