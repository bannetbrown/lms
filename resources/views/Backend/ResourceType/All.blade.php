@extends('Backend.main')
@section('content')
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            @if (count($errors) > 0)
                <div class="alert alert-error alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">All Resource Type</h3>

                    <div class="box-tools">
                        <a href="javascript:void(0);" class="btn btn-primary btn-flat" data-act="ajax-modal" data-title="Add Resource Type" data-append-id="AjaxModelContent" data-action-url="{{route("addResource")}}">
                            <i class="fa fa-plus-circle"></i> Add
                        </a>
                    </div>

                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="Datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th style="width: 80px; text-align: center;"><i class="fa fa-bars"></i> </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th style="width: 80px; text-align: center;"><i class="fa fa-bars"></i> </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </section>
        <!-- /.content -->
    </div>
@endsection


@push('script')
    <script type="text/javascript">
        $('#Datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{route('allResourceDatatable')}}',
            columns: [
                {data: 'id', name: 'id', visible : false},
                {data: 'title', name: 'title'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[0,'desc']],
            "pageLength": {{AppSetting::getRowsPerPage()}},
            "drawCallback": function( settings ) {
                $('.magnific-image').magnificPopup({type: 'image'});
            }
        });
    </script>
@endpush


