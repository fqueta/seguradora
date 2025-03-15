@php
  $subject = isset($details['subject']) ? $details['subject'] : @$details['saudacao'];
  $name = isset($details['name']) ? $details['name'] : false;
  $message = isset($details['message']) ? $details['message'] : false;
  $link_contato_site = isset($details['link_contato_site']) ? $details['link_contato_site'] : false;
@endphp
<x-mail::message>
# {!!$subject!!}
{!! $message !!}
@if($link_contato_site)
<x-mail::button :url="''">
{{__('Contate-nos')}}
</x-mail::button>
@endif

{{__('Obrigado')}},<br>
{{ config('app.name') }}
</x-mail::message>
