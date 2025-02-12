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
                    <a href="/db-assessment/companies/create" class="btn btn-info">Tambah Perusahaan</a> <br><br>
                    <div class="table-responsive">
                        <table class="table" id="companies-table">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 5%;">No</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Bidang Usaha</th>
                                    <th scope="col">No Telp Perusahaan</th>
                                    <th style="width: 25%;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                        <button type="submit" class="btn btn-info">Ya</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="show-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
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
                        @include('db_assessment.company.show')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </form>
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
        $(function() {
            var oTable = $('#companies-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('data/dbassessment-company-data') }}'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    // {data: 'business_field', name: 'business_field'},
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $('#companies-table').DataTable().on('click', 'button.delete', function() {
                var id = $(this).attr('id');
                $('#myForm').attr('action', '/db-assessment/companies/' + id);
                $('#delete-modal').modal('show')
            });

        });
    </script>
    <script type="text/javascript">
        $('#show-modal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var name = button.data('name')
            // var business_field = button.data('business_field')
            var address = button.data('address')
            var city = button.data('city')
            var phone = button.data('phone')
            var pic_name = button.data('pic_name')
            var pic_phone = button.data('pic_phone')
            var pic_mail = button.data('pic_mail')

            var modal = $(this)

            modal.find('.modal-body #name').val(name);
            modal.find('.modal-body #business_field').val(business_field);
            modal.find('.modal-body #address').val(address);
            modal.find('.modal-body #city').val(city);
            modal.find('.modal-body #phone').val(phone);
            modal.find('.modal-body #pic_name').val(pic_name);
            modal.find('.modal-body #pic_phone').val(pic_phone);
            modal.find('.modal-body #pic_mail').val(pic_mail);
        })
    </script>
@endsection
