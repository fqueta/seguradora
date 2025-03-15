@component('mail::message')
{{-- {{dd($user)}} --}}
@if (isset($type))
    @if($type=='notifica_superado')
        <h1>Olá {{ $user->name }}</h1>
        <p>Seu lance para o <b>{{$user->nome_leilao}}</b> foi superado </p>
        @component('mail::button',['url'=>$user->link_leilao])
        Novo Lance
        @endcomponent
        <small>Lembrando que para dar um lance é necessário estar logado!</small>
    @elseif ($type=='notifica_finalizado')
        @php
            echo $mensagem;
        @endphp
        @component('mail::button',['url'=>$user->link_leilao])
            {{__('Pagamento')}}
        @endcomponent
    @elseif ($type=='notific_update_admin')
        @php
            echo $mensagem;
        @endphp
        @component('mail::button',['url'=>@$user->link_leilao_admin])
            {{__('Editar Leilão')}}
        @endcomponent
    @elseif ($type=='notific_lance_seguidor')
        <h1>Olá {{ $user->name }}</h1>
        @php
            $msg = App\Qlib\Qlib::qoption('notific_lance_seguidor');
            $msg = str_replace('{nome_leilao}',$user->nome_leilao,$msg);
            $msg = str_replace('{link_leilao}',$user->link_leilao,$msg);
        @endphp
        <p>{!!$msg!!}</p>
        @component('mail::button',['url'=>$user->link_leilao])
        Acompanhar
        @endcomponent
        <small>Lembrando que para dar um lance é necessário estar logado!</small>

    @endif
@endif

@endcomponent
