@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola User</h1>
@stop

@section('content')
@if (session('exist'))
  <div class="alert alert-danger" style="margin-top: 1%">Email {{ session('exist') }} sudah terdaftar!</div>
@endif
<div class="card">
    <div class="card-body">
        <form id="myFrom" action="/assessment/users" method="POST">
            @csrf
          <div class="form-group">
            <label for="">Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Masukan Nama" value="{{old('name')}}">
            @error('name')
                <div class="alert alert-danger" style="margin-top: 1%">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="Masukan Email" value="{{old('email')}}">
            @error('email')
                <div class="alert alert-danger" style="margin-top: 1%">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="">Kata Sandi</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="Masukan Password">
            <small id="password" class="form-text text-muted">Minimal 8 karakter</small>
            @error('password')
                <div class="alert alert-danger" style="margin-top: 1%">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="">Konfirmasi Kata Sandi</label>
            <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" name="confirm_password" id="confirm-password" placeholder="Confirm Password">
            @error('confirm_password')
                <div class="alert alert-danger" style="margin-top: 1%">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <div class="form-group">
              <label for="">Role User</label>
              <select class="form-control @error('role') is-invalid @enderror" id="role" name="role">
                <option value="0" disabled selected>== Pilih Role User ==</option>
                <option value="admin" {{ old('role') == "admin" ? 'selected' : '' }}>Admin</option>
                <option value="data entry" {{ old('role') == "data entry" ? 'selected' : '' }}>Data Entry</option>
              </select>
              @error('role')
                <div class="alert alert-danger" style="margin-top: 1%">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="action" style="text-align: right">
              <button id="btn-back" type="button" class="btn btn-default">Kembali</button>
              <button id="btn-submit" type="button" class="btn btn-primary">Simpan</button>
          </div>
        </form>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script type="text/javascript">
        $(function() {
            // $('select#role option[value="0"]').attr('selected' , true)

            $('input#password').on('keyup' , function(){
                var value_pwd = $(this).val()
                var value_conf = $('input#confirm-password').val()
                if(value_conf == value_pwd && value_pwd != ""){
                    $('input#password').addClass('is-valid')
                    $('input#confirm-password').addClass('is-valid')
                }else{
                    $('input#password').removeClass('is-valid')
                    $('input#confirm-password').removeClass('is-valid')
                }
            })

            $('input#confirm-password').on('keyup' , function(){
                var value_conf = $(this).val()
                var value_pwd = $('input#password').val()

                if(value_pwd == value_conf && value_conf != ""){
                    $('input#password').removeClass('is-invalid')
                    $('input#confirm-password').removeClass('is-invalid')
                    $('input#password').addClass('is-valid')
                    $('input#confirm-password').addClass('is-valid')
                }else{
                    $('input#password').removeClass('is-valid')
                    $('input#confirm-password').removeClass('is-valid')
                }
            })


            $('button#btn-back').click(function(){
                window.location.href = "/assessment/users";
            })

            $('button#btn-submit').click(function(){
              var value_pwd = $('input#password').val()
              var value_conf = $('input#confirm-password').val()
              if(value_pwd == value_conf || value_conf != "" || value_pwd != ""){
                $('form#myFrom').submit()
              }else if(value_pwd != value_conf && value_pwd != "" && value_conf != ""){
                $('input#password').removeClass('is-valid')
                $('input#confirm-password').removeClass('is-valid')
                $('input#password').addClass('is-invalid')
                $('input#confirm-password').addClass('is-invalid')
              }

            })
          });
    </script>
@endsection
