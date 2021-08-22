@component('mail::message')
Hi, Andres

@component('mail::panel')
    {!! $content ?? '' !!}
@endcomponent

@component('mail::button', ['url' => route('download.stock.excel'), 'color' => 'green'])
    Download Stock File
@endcomponent

@component('mail::button', ['url' => route('download.shopify.import.excel'), 'color' => 'red'])
    Download Shopify File
@endcomponent

{{--$counter $totalProductProcessed, $totalImagesFound, $totalImagesNotFound,--}}
@component('mail::table')
    |Product Processed       | Images Found         | Images Not Found  |
    | ------------- |:-------------:| --------:|
    | {{@$counter[0]}}    | {{@$counter[1]}}      | {{@$counter[2]}}      |
@endcomponent
###Images not found is due to no INVENTORY.
{{--<a target="_blank" href="{{route('download.erroLogs.excel')}}">HERE</a>--}}

<br>
AMIGUI.CO email <br>
abc@abc.se
@endcomponent
