@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Asesi</h1>
@stop


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="/assessment/participants/create" class="btn btn-info" style="margin-bottom: 20px">Tambah
                        Asesi</a>
                    <div class="table-responsive">
                        <table class="table" id="participant-table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">No</th>
                                    <th>Nama</th>
                                    <th>Perusahaan</th>
                                    <th>Divisi</th>
                                    <th>Departemen</th>
                                    <th>Jabatan</th>
                                    <th>Tanggal Proyek</th>
                                    <th>Detail</th>
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
                    Apakah anda yakin ingin menghapus <label id="dynamic-text"></label>?
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

            var oTable = $('#participant-table').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    emptyTable: "Tidak ada data yang tersedia",
                },
                ajax: {
                    url: '{{ url('data/assessment-participant-data') }}'
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
                        data: 'division.name',
                        name: 'division.name'
                    },
                    {
                        data: 'departement.name',
                        name: 'departement.name'
                    },
                    {
                        data: 'position.name',
                        name: 'position.name'
                    },
                    {
                        data: 'project_date',
                        name: 'project_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $('#participant-table tbody').on('click', 'button.detail', function() {
                var tr = $(this).closest('tr');
                var row = oTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            function format(rowData) {
                var id = rowData.id;
                var div = $('<div/>')
                    .addClass('loading')
                    .text('Loadingg ....');

                $.ajax({
                    url: '/data/assessment-detail-participant/' + id,
                    data: {
                        name: rowData.name
                    },
                    success: function(json) {
                        div
                            .html(json)
                            .removeClass('loading');
                    }
                });

                return div;

            }

            $('#participant-table').DataTable().on('click', 'button.delete-participant', function() {
                var id = $(this).attr('id');
                $('#myForm').attr('action', '/assessment/participants/' + id);
                $('label#dynamic-text').text('Asesi')
                $('#delete-modal').modal('show')
            });


        });
    </script>
@endsection
