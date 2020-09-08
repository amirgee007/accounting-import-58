@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">

    <link href="{{asset('assets/plugins/bootstrap-fileinput/css/fileinput.css')}}" media="all" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" crossorigin="anonymous">
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Dashboard (Auto update after every hour) Tax: {{$setting->value}}%
                        <span class="badge badge-danger float-right">Last Updated Shopify file:{{@$lastUpdate->value}}</span></div>

                    <div class="card-body">

                        <div class="file-loading">
                            <input id="prod-images" name="images[]" type="file" accept="image/*" multiple>
                        </div>

                        <hr>

                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
                                <a type="" href="{{route('create.stock.excel')}}" class="btn btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Stock File
                                </a>

                                <a href="{{route('create.stock.files')}}" class="btn btn-danger">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Click to update
                                </a>

                                <a href="{{route('create.shopify.import.excel')}}" class="btn btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Shopify XLS File
                                </a>

                            </div>
                        </div>

                        <br>
                        <hr>
                        <br>
                        <form method="GET" action="{{route('home')}}">

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">LOAD any SKU images if needed</label>

                                <div class="col-md-5">
                                    <input type="number" value="{{request('is_sku')}}" class="form-control" name="is_sku" required>
                                </div>

                                <div class="col-md-3">
                                    {{--<button type="submit" class="btn btn-primary float-right">--}}
                                        {{--Load Images--}}
                                    {{--</button>--}}
                                </div>

                            </div>
                        </form>

                        @if(request('is_sku'))
                        <br>
                        <div class="form-group row mb-0">

                            <div class="container">
                                <div class="row">
                                    <div class="col-sm">
                                        <img width="200" src="https://www.w3schools.com/bootstrap/paris.jpg" alt="..." class="img-thumbnail">
                                    </div>

                                    <div class="col-sm">
                                        <img width="200" src="https://www.w3schools.com/bootstrap/paris.jpg" alt="..." class="img-thumbnail">
                                    </div>

                                    <div class="col-sm">
                                        <img width="200" src="https://www.w3schools.com/bootstrap/paris.jpg" alt="..." class="img-thumbnail">
                                    </div>
                                </div>
                            </div>

                        </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    @include('partials.toaster-js')


    <script src="{{asset('assets/plugins/bootstrap-fileinput/js/fileinput.js')}}" type="text/javascript"></script>

    <script>
        var $el1 = $("#prod-images");

        $el1.fileinput({

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            allowedFileExtensions: ["png", "jpg", "gif", "jpeg", "bmp", "tiff"],
            uploadAsync: false,
            showUpload: false,
            showDownload: true,
            showRemove: false,
            overwriteInitial: false,
            minFileCount: 1,
            maxFileCount: 50,
            initialPreviewAsData: true,
            initialPreviewFileType: 'image',
            initialPreview: [],
            initialPreviewConfig: [],
            uploadUrl: "/ajaxProdImageUpload",

            uploadExtraData: function() {
                return {
                    _token:"{{csrf_token()}}"
                };
            },

            deleteExtraData: function() {
                return {
                    _token:"{{csrf_token()}}"
                };
            },

            deleteUrl: "/ajaxProdImageDelete"

        }).on("filebatchselected", function (event, files) {
            $el1.fileinput("upload");
        }).on('filedeleteerror', function(event, data, msg) {
            console.log(msg,data);
            toastr.error("Something went wrong please contact admin .", "Error");
        }).on('filedeleted', function(event, key, jqXHR, data) {
            toastr.success("File successfully removed.", "Success");
        });

    </script>
@stop
