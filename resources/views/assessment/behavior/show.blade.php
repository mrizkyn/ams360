@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Perilaku Kunci</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="/assessment/behaviors" method="POST" id="prilaku">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Kompetensi</label>
                            <input type="hidden" name="competence_id" value="{{ $id }}">
                            <input type="type" name="name" readonly class="form-control" id="name"
                                value="{{ $name }}">
                        </div>
                        <div class="form-group">
                            <label for="definition">Definisi Kompetensi</label>
                            <textarea class="form-control" readonly name="definition" id="definition" rows="3">{{ $definition }}</textarea>
                        </div>
                        <hr>
                        <div class="form-group" style="margin-top: 1%">
                            <label for="description">Perilaku Kunci</label>
                            <div class="row">
                                <div class="col-sm-11">
                                    <input type="text"
                                        class="form-control description @error('description') is-invalid @enderror"
                                        name="description[]" id="description" placeholder="Masukan Perlaku Kunci">
                                    @error('description')
                                        <div class="alert alert-danger" style="margin-top: 10px">{{ $message }}</div>
                                    @enderror
                                    <div class="alert alert-danger d-none" style="margin-top: 10px">Perilaku Kunci tidak
                                        boleh kosong</div>
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-info btn-md btnStatic" id="tmb-prilaku"><i
                                            class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div class="action" style="text-align: right">
                        <a href="/assessment/competencies" class="btn btn-default">Kembali</a>
                        <button id="submit" type="text" class="btn btn-info">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .btnDynamic {
            background-color: Transparent;
            background-repeat: no-repeat;
            border: none;
            cursor: pointer;
            overflow: hidden;
            outline: none;
            color: red;
        }

        .alert {
            margin-top: 1%
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var id = 0
            $('#tmb-prilaku').on('click', function() {
                id++
                var formgroup = $("<div/>", {
                    class: "form-group",
                    id: "div" + id
                });
                // jangan d hapus takut nya ada revisi minta d tambah label
                // formgroup.append($("<label>", {
                //   class: "col-sm-2 control-label",
                //   text: "Perilaku Kunci"
                // }));
                var row = $("<div/>", {
                    class: "row"
                })
                var colsm_10 = $("<div/>", {
                    class: "col-sm-11"
                });
                var colsm_2 = $("<div/>", {
                    class: "col-sm-1"
                });
                var input = $("<input/>", {
                    type: "text",
                    class: "form-control description",
                    id: id,
                    name: "description[]",
                    placeholder: "Masukan Perilaku Kunci"
                });
                var alert = $('<div/>', {
                    class: 'alert alert-danger d-none',
                    id: 'alert' + id
                });
                alert.append('Perilaku Kunci tidak boleh kosong')
                var button = $('<button/>', {
                    class: 'button btn btn-md btn-danger',
                    id: id,
                    type: "button"
                });

                button.append('<i class="fas fa-minus"> </i>');
                row.append(colsm_10, colsm_2)
                colsm_10.append(input, alert);
                colsm_2.append(button);
                formgroup.append(row);
                $('#prilaku').append(formgroup)
            });

            $(document).on('click', 'button.button', function() {
                var id = $(this).attr('id')
                document.getElementById('div' + id).remove()
            });

            $('button#submit').on('click', function() {
                var perilaku = document.getElementsByClassName('description')
                var alert = document.getElementsByClassName('alert')
                var perilaku_array = []
                var alert_array = []
                for (let index = 0; index < perilaku.length; index++) {
                    var value = perilaku[index].value
                    if (value == "") {
                        perilaku_array.push(perilaku[index])
                        alert_array.push(alert[index])
                    } else {
                        perilaku[index].classList.remove('is-invalid')
                        alert[index].classList.add('d-none')
                    }
                }
                if (perilaku_array != 0) {
                    for (let index = 0; index < perilaku_array.length; index++) {
                        perilaku_array[index].classList.add('is-invalid')
                        alert_array[index].classList.remove('d-none')
                    }
                } else {
                    $('form#prilaku').submit()
                }

            });

        });
    </script>
@endsection
