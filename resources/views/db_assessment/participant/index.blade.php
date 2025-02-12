@extends('adminlte::page')

@section('title', 'Asesi')

@section('content_header')
    <h1 class="m-0 text-dark">Asesi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="/db-assessment/participants/create" class="btn btn-info">Tambah Asesi</a> <br><br>
                    <div class="table-responsive">
                        <table class="table" id="participants-table">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">NIK</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
            var oTable = $('#participants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('data/dbassessment-particpant-data') }}'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'identity_number',
                        name: 'identity_number'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'company.name',
                        name: 'company.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $('#participants-table').DataTable().on('click', 'button.delete', function() {
                var id = $(this).attr('id');
                $('#myForm').attr('action', '/db-assessment/participants/' + id);
                $('#delete-modal').modal('show')
            });

        });
    </script>
@endsection
