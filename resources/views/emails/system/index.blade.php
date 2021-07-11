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

<br><br>
Testing Emails <br>
E-Mail: abc@abc.se
@endcomponent
