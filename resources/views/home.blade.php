@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">

    <link href="{{asset('assets/plugins/bootstrap-fileinput/css/fileinput.css')}}" media="all" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" crossorigin="anonymous">


    <link rel="stylesheet" type="text/css" href="{{asset('dropzone/dist/min/dropzone.min.css')}}">

@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Dashboard (Auto update Hourly) Tax: {{$setting->value}}%
                        <span class="badge badge-danger float-right">Last Updated file:{{@$lastUpdate->value}}</span></div>

                    <div class="card-body">

                        <form action="{{route('add_img')}}" class='dropzone' ></form>

                        <hr>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <a type="" href="{{route('reset.all.images')}}"  class="btn btn-sm btn-danger">
                                    <i class="fa fa-download" aria-hidden="true"></i> Reset All Images
                                </a>

                                <a type="" href="{{route('create.stock.excel')}}" class="btn-sm btn btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Stock File
                                </a>

                                <a href="{{route('create.shopify.import.excel')}}" class="btn btn-sm btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Shopify XLS File
                                </a>

                                <a href="{{route('create.stock.files')}}" class="btn btn-sm btn-danger">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Click to REFRESH
                                </a>

                            </div>
                        </div>

                        <br>
                        <hr>
                        <br>
                        <form method="GET" action="{{route('home')}}">

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Test SKU images</label>

                                <div class="col-md-5">
                                    <input type="number" value="{{request('is_sku')}}" class="form-control" placeholder="08541845695" name="is_sku" required>
                                </div>

                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary float-right">
                                        Load Images
                                    </button>
                                </div>

                            </div>
                        </form>

                        @if(request('is_sku'))
                        <br>
                        <div class="form-group row mb-0">

                            <div class="container">
                                <div class="row">
                                    @if (count($files))
                                        @foreach ($files as $index => $file)
                                        <div class="col-sm">
                                            <img width="200" src="{{url(str_replace('public' ,'storage' ,$file))}}" alt="..." class="img-thumbnail">
                                        </div>
                                        @endforeach
                                    @else
                                        <h5 class="ml-4 text-danger">Sorry No image found for this SKU please upload again or contact admin.</h5>
                                    @endif
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

    <script src="{{asset('dropzone/dist/min/dropzone.min.js')}}" type="text/javascript"></script>

    <script>
        var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone(".dropzone",{
            maxFilesize: 1,  // 3 mb
            acceptedFiles: ".jpeg,.jpg,.png,.JPG,.JPEG",
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", CSRF_TOKEN);
        });
    </script>
@stop
