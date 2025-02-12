@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Bidang Usaha</h1>
@stop


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="/assessment/business-fields/create" class="btn btn-info" style="margin-bottom: 20px">Tambah
                        Bidang Usaha</a>
                    <div class="table-responsive">
                        <table class="table" id="departement-table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">No</th>
                                    <th style="width: 60%">Nama Bidang Usaha</th>
                                    <th>Actions</th>
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
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css" />
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script type="text/javascript">
        $(function() {

            // load datatabel
            var oTable = $('#departement-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('data/assessment-business-data') }}'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            // event for delete departemen
            $('#departement-table').DataTable().on('click', 'button.delete', function() {
                var id = $(this).attr('id');
                $('#myForm').attr('action', '/assessment/business-fields/' + id);
                $('#delete-modal').modal('show')
            });
        });
    </script>
@endsection
