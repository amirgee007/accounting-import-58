@component('mail::message')
Hi, Andres

@component('mail::panel')
    {!! $content ?? '' !!}
@endcomponent

<br><br>
Testing Emails <br>
E-Mail: abc@abc.se
@endcomponent
