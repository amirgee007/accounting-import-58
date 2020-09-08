{{--@extends('layouts.app')--}}

{{--@section('content')--}}
    {{--<div class="container">--}}
        {{--<div class="row justify-content-center">--}}
            {{--<div class="col-md-10">--}}
                {{--<div class="card">--}}
                    {{--<div class="card-header">Dashboard (Auto update after every hour)--}}
                        {{--<span class="badge badge-danger float-right">Last Updated Shopify file:{{@$lastUpdate->value}}</span></div>--}}

                    {{--<div class="card-body">--}}
                        {{--<form method="POST" action="{{route('tax.update')}}">--}}
                            {{--@csrf--}}

                            {{--<div class="form-group row">--}}
                                {{--<label for="name" class="col-md-4 col-form-label text-md-right">Tax Percentage</label>--}}

                                {{--<div class="col-md-8">--}}
                                    {{--<input type="number" value="{{$setting->value}}" min="1" max="100" class="form-control{{ $errors->has('tax') ? ' is-invalid' : '' }}" name="tax" required autofocus>--}}
                                    {{--<small>*Please write value <= 100 here only</small>--}}
                                {{--</div>--}}

                            {{--</div>--}}

                            {{--<div class="form-group row mb-0">--}}
                                {{--<div class="col-md-8 offset-md-4">--}}
                                    {{--<button type="submit" class="btn btn-primary float-right">--}}
                                        {{--Update Tax--}}
                                    {{--</button>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</form>--}}
                        {{--<hr>--}}

                        {{--<div class="form-group row mb-0">--}}
                            {{--<div class="col-md-10 offset-md-2">--}}
                                {{--<a type="" href="{{route('create.stock.excel')}}" class="btn btn-success">--}}
                                    {{--<i class="fa fa-download" aria-hidden="true"></i> Download Stock File--}}
                                {{--</a>--}}

                                {{--<a href="{{route('create.stock.files')}}" class="btn btn-danger">--}}
                                    {{--<i class="fa fa-refresh" aria-hidden="true"></i> Click to update now--}}
                                {{--</a>--}}

                                {{--<a href="{{route('create.shopify.import.excel')}}" class="btn btn-success">--}}
                                    {{--<i class="fa fa-download" aria-hidden="true"></i> Download Shopify Excel File--}}
                                {{--</a>--}}

                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
{{--@endsection--}}

{{--@section('scripts')--}}
    {{--@include('partials.toaster-js')--}}
{{--@stop--}}
