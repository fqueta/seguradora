@component('mail::message')

<p>Prezado(a) - {{ $user['name'] }},</p>
<p>
    Seja bem vindo(a) à {{ $empresa }}<br>
    Com esse cadastro você poderá ter acesso a vários serviços de nosso portal, bem como gerenciar os seus consentimentos e preferências.<br>
    Para ativar o seu cadastro use no botão abaixo para confirmar o seu e-mail:
</p>
<p>
    Observação: A confirmação do seu e-mail é obrigatória.
</p>
<p>
    * Este é um e-mail automático do sistema, não responda a este e-mail pois ele não será lido.
</p>
<p style="text-align: center">
    Atenciosamente
</p>
<p style="text-align: center">
    {{$empresa}}
</p>
    @component('mail::button',['url'=>$link_confirma_email])
        Confirmar Meu E-mail
    @endcomponent,

@endcomponent
