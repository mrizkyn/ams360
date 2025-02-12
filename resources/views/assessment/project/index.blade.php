@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Proyek</h1>
@stop

@section('content')
    <p>
        <a href="projects/create" class="btn btn-info">Tambah Proyek</a>
    </p>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="projects-table">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Proyek</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Tanggal Awal</th>
                                    <th scope="col">Tanggal Akhir</th>
                                    <th scope="col">Status</th>
                                    <th></th>
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

    <div class="modal fade" id="update-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Pemberitahuan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Proyek yang sudah selesai dan dalam proses tidak dapat diubah.
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
            var oTable = $('#projects-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('data/assessment-project-data') }}'
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
                        data: 'company.name',
                        name: 'company.name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $('#projects-table').DataTable().on('click', 'button.delete', function() {
                var id = $(this).attr('id');
                $('#myForm').attr('action', '/assessment/projects/' + id);
                $('#delete-modal').modal('show')
            });

            $('#projects-table').DataTable().on('click', 'button.update', function() {
                var id = $(this).attr('id');
                var status = $(this).attr('data-status');
                if (status != "Draft") {
                    $('#update-modal').modal('show')
                } else {
                    window.location.replace(`/assessment/projects/${id}/edit`);
                }
            });

        });
    </script>
@endsection
