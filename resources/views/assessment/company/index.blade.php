@extends('adminlte::page')

@section('title', 'Perusahaan')

@section('content_header')
    <h1 class="m-0 text-dark">Perusahaan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="/assessment/companies/create" class="btn btn-success">Tambah Perusahaan</a> <br><br>
                    <div class="table-responsive">
                      <table class="table" id="companies-table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" style="width: 5%;">No</th>
                            <th scope="col" style="width: 30%;">Nama Perusahaan</th>
                            <th scope="col" style="width: 20%;">Bidang Usaha</th>
                            <th scope="col" style="width: 20%;">No Telp Perusahaan</th>
                            <th style="width: 25%;">Action</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="show-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Detail Perusahaan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="" method="POST" id="myFormShow">
            @csrf
            @method('GET')
            <div class="modal-body">
              @include('assessment.company.show')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
              <button type="submit" class="btn btn-primary">Ya</button>
            </form>
          </div>
        </div>
      </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script type="text/javascript">
        $(function() {
            var oTable = $('#companies-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url("data/assessment-company-data") }}'
                },
                columns: [
                {data: 'DT_RowIndex' , name: 'DT_RowIndex'},
                {data: 'name', name: 'name'},
                {data: 'business_field.name', name: 'business_field.name'},
                {data: 'phone', name: 'phone'},
                {data: 'action', name:'action', orderable: false, searchable: false},
            ],
            });

            $('#companies-table').DataTable().on('click' , 'button.delete' , function(){
              var id = $(this).attr('id');
              $('#myForm').attr('action' , '/assessment/companies/'+id);
              $('#delete-modal').modal('show')
            });

        });
    </script>
    <script type="text/javascript">
      $('#show-modal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget)
          var nama = button.data('nama')
          var bf = button.data('bf')
          var address = button.data('address')
          var city = button.data('city')
          var phone = button.data('phone')
          var picname = button.data('picname')
          var picphone = button.data('picphone')
          var picmail = button.data('picmail')

          var modal = $(this)

          modal.find('.modal-body #namaPerusahaan').val(nama);
          modal.find('.modal-body #bidangIndustri').val(bf);
          modal.find('.modal-body #alamatPerusahaan').val(address);
          modal.find('.modal-body #kotaPerusahaan').val(city);
          modal.find('.modal-body #noTelpPerusahaan').val(phone);
          modal.find('.modal-body #namaPIC').val(picname);
          modal.find('.modal-body #noTelpPIC').val(picphone);
          modal.find('.modal-body #emailPIC').val(picmail);
        })
    </script>
@endsection
