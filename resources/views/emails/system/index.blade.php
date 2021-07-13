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

<br>

Also if you want download error logs please click here and download the file if exist.
<a target="_blank" href="{{route('download.erroLogs.excel')}}">HERE</a>

<br>
Testing Emails <br>
E-Mail: abc@abc.se
@endcomponent
