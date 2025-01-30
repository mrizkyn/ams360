@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 text-dark">Kompetensi</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                  <label for="name">Nama Kompetensi</label>
                  <select class="form-control" id="name">
                    <option value="0" selected>Pilih Nama Kompetensi</option>
                    @foreach ($competencies as $competence)
                        <option value="{{ $competence->id }}">{{ $competence->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="action" style="text-align: right">
                    <a href="/assessment/competencies/create" class="btn btn-success">Tambah Kompetensi</a>
                    <button type="button" id="ubah" class="btn btn-info">Ubah Kompetensi</button>
                </div>
                <div class="form-group">
                  <label for="definisi">Definisi Kompetensi</label>
                  <textarea readonly class="form-control" id="definition" rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
  <div class="col-12">
      <div class="card">
          <div class="card-body">
              <div class="form-group">
                  <label for="behavior">Perilaku Kunci</label>
                  <div class="action">
                    <button type="button" id="add-behavior" class="btn btn-success" style="margin-bottom: 15px">Tambah Perilaku Kunci</button>
                  </div>
                  <div class="table-responsive">
                    <table class="table" id="competence-table" style="width: 100%">
                      <thead class="thead-dark">
                        <tr>
                          <th style="width: 10%">No</th>
                          <th style="width: 60%">Perilaku Kunci</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>


{{-- modal delete --}}
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
        Apakah anda yakin ingin menghapus <label id="dynamic-text">test</label>?
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
{{-- end modal --}}

{{-- modal warning --}}
<div class="modal fade" id="warning-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Pemberitahuan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Anda harus memilih dahulu kompetensi!
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Ya</button>
      </div>
    </div>
  </div>
</div>
{{-- end modal --}}

@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script type="text/javascript">
        $(function() {
            $('#competence-table').DataTable({
              destroy : true
            });
            //change value definition trigger select id = name
            $('select#name').prop('selectedIndex',0);
            $('textarea#definition').val('');
            $('select#name').change(function(){
                if($(this).val() == 0){
                    $('textarea#definition').val('');
                    $('table#competence-table').DataTable();
                    var oTable = $('#competence-table').DataTable({
                        processing: true,
                        serverSide: true,
                        destroy : true,
                        ajax: {
                            url: '/data/assessment-behavior-data/'+0
                        },
                        columns: [
                        {data: 'id', name: 'id'},
                        {data: 'description', name: 'description'},
                        {data: 'action', name:'action', orderable: false, searchable: false},
                    ],
                    });
                }else{
                    var id = $(this).val();
                    var url = '/data/assessment-definition-data/' + id;
                    $.ajax({
                        url: url ,
                        type:'GET' ,
                        success:function(response){
                            var json = $.parseJSON(response);
                            $('textarea#definition').val(json.definition);
                        },
                        dataType:'text'
                    });

                    var oTable = $('#competence-table').DataTable({
                        processing: true,
                        serverSide: true,
                        destroy : true,
                        ajax: {
                            url: '/data/assessment-behavior-data/'+id
                        },
                        columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        {data: 'description', name: 'description'},
                        {data: 'action', name:'action', orderable: false, searchable: false},
                    ],
                    });
                }
            });

            // event for redirect to form ubah
            $('button#ubah').click(function(){
                var id = $( "select#name option:selected" ).val();
                if( id > 0){
                    window.location.href = "/assessment/competencies/"+id+"/edit";
                }else{
                  $('#warning-modal').modal('show')
                }
            });

            // event for delete competence
            $('button#hapus').click(function(){
                var id = $( "select#name option:selected" ).val();
                var name = $( "select#name option:selected" ).text();
                if(id > 0){
                    $('#myForm').attr('action' , '/assessment/competencies/'+id)
                    $('label#dynamic-text').text('Kompetensi')
                    $('#delete-modal').modal('show')
                }else{
                  $('#warning-modal').modal('show')
                }
            });

            // event for add behavior
            $('button#add-behavior').click(function(){
              var id = $( "select#name option:selected" ).val();
                if( id > 0){
                    window.location.href = "/assessment/behaviors/"+id;
                }else{
                  $('#warning-modal').modal('show')
                }
            });

            // event for delete key behavior
            $('#competence-table').DataTable().on('click' , 'button.delete' , function(){
              var id = $(this).attr('id');
              $('#myForm').attr('action' , '/assessment/behaviors/'+id);
              $('label#dynamic-text').text('Perilaku Kunci')
              $('#delete-modal').modal('show')
            });

        });
    </script>
@endsection
