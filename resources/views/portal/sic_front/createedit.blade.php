@extends('layouts.principal')

@section('nav')
    @include('portal.nav')
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 mens">
        </div>
    </div>
    <div class="row mt-5 pt-4">
        @can('is_user_front_v')
            <div class = "col-12 page-header">
                <h1 class="text-center">
                    {{$titulo}}
                </h1>
            </div>
            @php
                $_GET['redirect'] = route('internautas.index');
                //$config['event'] = 'enctype="multipart/form-data"';
            @endphp

            {{App\Qlib\Qlib::formulario([
                'campos'=>$campos,
                'config'=>$config,
                'value'=>$value,
            ])}}
        @elsecannot('is_user_front_v')
            <div class="col-12">
                <h6 class="text-center"> {{__('Somente internautas com cadastro confirmados podem usar este serviço')}} </h6>
                <p>{{__('Para confirmar seu cadastro acesse a caixa de entrada do seu email e clique no botão de confirmação. Não esqueça de verificar a caixa de span ou o lixo eletrônico caso não esteja na caixa de entrada')}}</p>
            </div>
            <div class="text-center col-12">
                <button type="button" onclick="resend_email_sic();" class="btn btn-primary">{{__('Reenviar e-mail')}} </button>
            </div>
        @endcan
    </div>
</div>
@endsection

@section('css')
    @include('portal.css')
@endsection

@section('js')
    @include('portal.js')
    @include('portal.sic_front.js_submit')
@endsection
