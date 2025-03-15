@component('mail::message')

<h1>Olá {{ $user->name }}</h1>
<p>Seja bem vindo em nossa plataforma de leilões </p>
<p>
    para completar ser cadastro é necessário verificar seu E-mail <b>{{$user->email}}</b>
</p>
    @component('mail::button',['url'=>asset('/')])
        Verificar email
    @endcomponent,

@endcomponent
