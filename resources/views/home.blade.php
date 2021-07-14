@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">

    <link href="{{asset('assets/plugins/bootstrap-fileinput/css/fileinput.css')}}" media="all" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" crossorigin="anonymous">


    <link rel="stylesheet" type="text/css" href="{{asset('dropzone/dist/min/dropzone.min.css')}}">

    <style>
        .containerCustom {
            font-size: 70px;
            display: flex;
            justify-content: center;
            color: firebrick;
        }
    </style>
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Dashboard (Auto update Hourly)-> Tax: {{$tax->value}}%
                        <span class="badge badge-danger float-right">Last Updated file:{{@$lastUpdate->value}}</span></div>

                    <div class="card-body">

                        <form action="{{route('add_img')}}" class='dropzone' ></form>

                        <hr>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <a type="" href="{{route('reset.all.images')}}"  class="btn btn-sm btn-danger">
                                    <i class="fa fa-download" aria-hidden="true"></i> Reset All Image
                                </a>

                                <a type="" href="{{route('download.stock.excel')}}" class="btn-sm btn btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Stock File
                                </a>

                                <a href="{{route('download.shopify.import.excel')}}" class="btn btn-sm btn-success">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Shopify File
                                </a>

                                <a id="mybutton" href="{{route('process.images.files.excel')}}" class="btn btn-sm btn-danger processImages">
                                    <span id="imageLoader"><i class="fa fa-refresh" aria-hidden="true"></i></span> Process images into XLS
                                </a>


                            </div>

                            <div class="col-md-12">
                                <div class="containerCustom" id="timerDiv"></div>
                            </div>
                        </div>

                        <hr>

                        <form method="post" action="{{route('save.settings')}}">

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Write Tags Here</label>

                                <div class="col-md-5">
                                    <input type="text" value="{{@$tags->value}}" class="form-control" placeholder="a, b, c" name="tags" required>
                                </div>

                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success btn-sm float-right">
                                        Save All Tags Here
                                    </button>
                                </div>

                            </div>
                        </form>

                        <form method="post" action="{{route('save.settings')}}">

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Write Email Here</label>

                                <div class="col-md-5">
                                    <input type="email" value="{{@$email->value}}" class="form-control" placeholder="amir@test.com" name="adminEmail" required>
                                </div>

                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success btn-sm float-right">
                                        Save Admin Email
                                    </button>
                                </div>

                            </div>
                        </form>


                        <hr>
                        <form method="GET" action="{{route('home')}}">

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Test SKU images</label>

                                <div class="col-md-5">
                                    <input type="number" value="{{request('is_sku')}}" class="form-control" placeholder="08541845695" name="is_sku" required>
                                </div>

                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm float-right">
                                        Load Images Here
                                    </button>
                                </div>

                            </div>
                        </form>

                        <hr>
                        <form class="form-inline" action="{{route('rename.files.sku')}}" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}

                            @if($errors->any())
                                {!!   implode('', $errors->all('<div class="text-danger">:message</div>')) !!}
                            @endif

                            <div class="form-group mb-2">
                                <label for="exampleFormControlFile1">Zip images</label>
                                <input type="file" required name="images_zip" class="form-control-file" id="zipFiles">
                            </div>

                            <div class="form-group mb-2">
                                <label for="exampleFormControlFile1">SKUs XLS</label>
                                <input type="file" required name="sku_file" class="form-control-file" id="excelSku">
                            </div>

                            <br>
                            <input type="submit" value="Rename XLS File" class="btn btn-primary float-right">
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

                                                <a target="_blank" href="{{url(str_replace('public' ,'storage' ,$file))}}"><small>View In BROWSER</small></a>

                                                <br>
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


        var timer = null;
        var time = 0;
        $('#mybutton').click(function() {
//            time = 1800;
//            showTimer();
//            timer = setInterval(showTimer, 1000);

            toastr.success('Job has been scheduled please check in 30 minutes...!');

            setTimeout(function(){
                window.location.reload(1);
            }, 5000);


        });
//
//        function showTimer() {
//            if (time < 0) {
//                clearInterval(timer);
//                return;
//            }
//            function pad(value) {
//                return (value < 10 ? '0' : '') + value;
//            }
//            $('#timerDiv').text(Math.floor(time / 60) + ':' + pad(time % 60));
//            $('#imageLoader').html('<i class="fa fa-refresh fa-spin" aria-hidden="true"></i>');
//
//            time--;
//        }

    </script>
@stop
