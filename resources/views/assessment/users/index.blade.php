@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola User</h1>
@stop

@section('content')
@if (session('success-reset'))
  <div class="alert alert-success" style="margin-top: 1%">Akun {{ session('success-reset') }} berhasil ubah password</div>
@endif
<div class="card">
    <div class="card-body">
        <a href="/assessment/users/create" class="btn btn-success" style="margin-bottom: 1%">Tambah User</a>
        <div class="table-responsive">
          <table class="table" id="table-users" style="width: 100%">
              <thead class="thead-dark"> 
                  <tr>
                      <th style="width: 2%">No</th>
                      <th style="width: 25%">Nama</th>
                      <th style="width: 25%">Email</th>
                      <th style="width: 15%">Role</th>
                      <th style="">Action</th>
                  </tr>
              </thead>
          </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ubah-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Ubah User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="" method="POST" id="myForm-ubah">
        @csrf
        @method('PATCH')
      <div class="modal-body"> 
        <input type="hidden" id="id-user-ubah">
        <div class="form-group">
          <label for="">Nama</label>
          <input type="text" name="name" class="form-control" id="name-modal">
          <div class="alert alert-danger d-none" id="alert-name" style="margin-top: 1%">Harap isi field nama</div>
        </div>
        <div class="form-group">
          <label for="">Email</label>
          <input type="text" name="email" class="form-control" id="email-modal">
          <div class="alert alert-danger d-none" id="alert-email" style="margin-top: 1%">Harap isi field email</div>
        </div>
        <div class="form-group">
            <div class="form-group">
              <label for="">Role User</label>
              <select name="role" class="form-control" id="role-modal">
                <option value="data entry">Data Entry</option>
                <option value="admin">Admin</option>
              </select>
            </div>
        </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
          <button type="button" id="btnUbah" class="btn btn-primary">Ya</button>
        </div>
    </form>
    </div>
  </div>
</div>

<div class="modal fade" id="ubah-pass-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Ubah Password <label id="user"></label></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form  id="myForm-pass" method="POST">
      @csrf
      @method('PATCH')
      <input type="hidden" name="id" id="id-user">
      <input type="hidden" name="flag" value="reset">
      <div class="modal-body"> 
        <div class="form-group">
          <label for="">Password</label>
          <input name="pass" type="password" class="form-control" id="pass-modal">
          <small id="password" class="form-text text-muted">Minimal 8 karakter</small>
          <div id="empty" class="invalid-feedback">
            Password tidak boleh kosong
          </div>
          <div id="match" class="invalid-feedback">
            Password tidak sama dengan Confrim Password
          </div>
          <div id="count" class="invalid-feedback">
            Password minimal 8 karakter
          </div>
        </div>
        <div class="form-group">
          <label for="">Confirm Password</label>
          <input name="pass_conf" type="password" class="form-control" id="conf-modal">
        </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
          <button type="button" class="btn btn-primary" id="btnReset">Ya</button>
        </div>
    </form>
    </div>
  </div>
</div>

<div class="modal fade" id="hapus-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Konfirmasi Hapus</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form  method="POST" id="myForm-hapus">
      <div class="modal-body"> 
        <label for="">Apakah anda yakin ingin menghapus akun ini?</label>
      <div class="modal-footer">
          @csrf
          @method('DELETE')
          <input type="hidden" id="id-user-hapus">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
          <button type="button" id="btnHapus" class="btn btn-primary">Ya</button>
        </div>
    </form>
    </div>
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
            var oTable = $('#table-users').DataTable({
                processing: true,
                serverSide: true,
                destroy : true,
                ajax: {
                    url: '/data/assessment-user-data/'
                },
                columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'role', name: 'role'},
                {data: 'action', name:'action', orderable: false, searchable: false},
            ],
            });

            $('#table-users').DataTable().on('click' , 'button.ubah' , function(){
                var id = $(this).attr('id')
                var url = '/data/assessment-user-detail/'+id
                $.ajax({
                    url : url , 
                    type : 'GET' , 
                    dataType : 'json' , 
                    success:function(response){
                        document.getElementById('id-user-ubah').value = response.id
                        document.getElementById('name-modal').value = response.name
                        document.getElementById('email-modal').value = response.email
                        document.getElementById('role-modal').value = response.role
                    }
                })
                $('#ubah-modal').modal('show')
            })
            
            $('button#btnUbah').on('click' , function(){
                var id = document.getElementById('id-user-ubah').value
                var name = document.getElementById('name-modal').value
                var email = document.getElementById('email-modal').value
                if(name != "" & email != "" ){
                  document.getElementById('myForm-ubah').setAttribute('action' , '/assessment/users/'+id)
                  document.getElementById('myForm-ubah').submit()
                }else{
                  document.getElementById('name-modal').classList.add('is-invalid')
                  document.getElementById('email-modal').classList.add('is-invalid')
                  document.getElementById('alert-name').classList.remove('d-none')
                  document.getElementById('alert-email').classList.remove('d-none')
                  
                }
            });

            $('#table-users').DataTable().on('click' , 'button.hapus' , function(){
                var id = $(this).attr('id')
                $('#id-user-hapus').attr('value' , id)
                $('#hapus-modal').modal('show')
            })

            $('button#btnHapus').on('click' , function(){
                var id = $('#id-user-hapus').val()
                $('#myForm-hapus').attr('action' , '/assessment/users/'+id)
                $('#myForm-hapus').submit()
            });

            $('#table-users').DataTable().on('click' , 'button.ubah-pass' , function(){
              var id = $(this).attr('id')      
              $('#id-user').attr('value' , id)
              $('#myForm-pass').attr('action' , '/assessment/users/'+id+'')
              $('#ubah-pass-modal').modal('show')
            })

            $('#pass-modal').on('keyup' , function(){
              var value_pass = $(this).val()
              var value_conf = $('#conf-modal').val()
              $('#empty').removeClass('d-none')
              $('#empty').addClass('d-none')
              $('#pass-modal').removeClass('is-invalid')
              $('#conf-modal').removeClass('is-invalid')
              if(value_pass == value_conf && value_pass != "" && value_conf.length >= 8 && value_pass.length >= 8){
                  $('#pass-modal').addClass('is-valid')
                  $('#conf-modal').addClass('is-valid')
              }else{
                  $('#pass-modal').removeClass('is-valid')
                  $('#conf-modal').removeClass('is-valid')
              }
            })

            $('#conf-modal').on('keyup' , function(){
              var value_conf = $(this).val()
              var value_pass = $('#pass-modal').val()
              $('#empty').removeClass('d-none')
              $('#empty').addClass('d-none')
              $('#pass-modal').removeClass('is-invalid')
              $('#conf-modal').removeClass('is-invalid')
              if(value_conf == value_pass && value_conf != "" && value_conf.length >= 8 && value_pass.length >= 8){
                  $('#pass-modal').addClass('is-valid')
                  $('#conf-modal').addClass('is-valid')
              }else{
                  $('#pass-modal').removeClass('is-valid')
                  $('#conf-modal').removeClass('is-valid')
              }
            })

            $('#btnReset').on('click' , function(){
              var id = $('#id-user').val()
              var value_pass = $('#pass-modal').val()
              var value_conf = $('#conf-modal').val()
              if(value_pass == value_conf && value_pass != "" && value_conf != "" && value_conf.length >= 8 && value_pass.length >= 8 ){
                $('#myForm-pass').attr('action' , '/assessment/users/'+id)
                $('#myForm-pass').submit()
              }else if(value_pass == "" && value_conf == ""){
                $('#pass-modal').addClass('is-invalid')
                $('#conf-modal').addClass('is-invalid')
                $('#match').addClass('d-none')
                $('#count').addClass('d-none')
                $('#empty').removeClass('d-none')
              }else if(value_pass != "" && value_conf != "" && value_conf.length >= 8 && value_pass.length >= 8){
                $('#pass-modal').addClass('is-invalid')
                $('#conf-modal').addClass('is-invalid')
                $('#empty').addClass('d-none')
                $('#count').addClass('d-none')
                $('#match').removeClass('d-none')
              }else{
                $('#pass-modal').addClass('is-invalid')
                $('#conf-modal').addClass('is-invalid')
                $('#empty').addClass('d-none')
                $('#match').addClass('d-none')
              }
            })

        });
    </script>
@endsection
