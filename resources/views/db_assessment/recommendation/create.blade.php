@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 text-dark">Tambah Rekomendasi</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <form id="add" action="/db-assessment/recommendations" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <label for="name">Nama Rekomendasi</label>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <input type="text" name="name"
                            class="form-control input-name @error('name') is-invalid @enderror" id="name"
                            placeholder="Masukkan Nama Rekomendasi" value="{{old('name')}}">
                        @error('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <label for="name">Item Rekomendasi</label>
                    <button type="button" class="btn btn-default float-right tambah" id="tambah">Tambah Item</button>
                    <button type="button" class="btn btn-default float-right hapusButton" id="hapusButton"
                        style="margin-right: 5px">hapus Item</button>
                </div>
                <div class="card-body">
                    <div class="form-group baris-baru">
                        <input type="text" class="form-control input-name @error('itemName') is-invalid @enderror"
                            id="itemName[]" placeholder="Masukkan Item Rekomendasi" name="itemName[]"
                            value="{{old('itemName')}}">
                        @error('itemName')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" id="save" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
        var x = 0;
        $("#hapusButton").hide();
        $("#tambah").click(function (e) {
            $("#hapusButton").show();
            if (x < 10) {
                $(".baris-baru").append('<div style="margin-top: 10px;" class="form' + x +
                    '"><input type="text" class="form-control input-name @error('
                    itemName ') is-invalid @enderror" id="itemName[]" placeholder="Masukkan Item Rekomendasi" name="itemName[]" value="{{old('
                    itemName ')}}">@error('
                    itemName ')<div class="alert alert-danger">{{ $message }}</div>@enderror</div>');
                x++;
            }
        });

        $("#hapusButton").click(function (e) {
            console.log(x);
            if (x > 0) {
                x--;
                $('.form' + x + '').remove();
            }
        });

        $('#save').on('click', function () {
            var valid = true
            var input = document.getElementsByClassName('input-name')
            console.log(input);

            for (let index = 0; index < input.length; index++) {
                if (input[index].value == '') {
                    input[index].classList.add('is-invalid')
                    valid = false
                } else {
                    input[index].classList.remove('is-invalid')
                }

            }

            if (valid) {
                $('#add').submit()
            }
        })
    });
</script>
@stop