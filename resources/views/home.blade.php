@extends('adminlte::page')

@section('title')
{{config('app.name')}} {{config('app.version')}} - Painel
@stop
@section('footer')
    @include('footer')
@stop

@section('content_header')

@stop

@section('content')
    @include('admin.partes.header')
    {{-- <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 tit-sep">Painel</h1>
        </div>
        <div class="col-sm-6 text-right">
            <div class="btn-group" role="group" aria-label="actions">
                <a href="https://cmd.databrasil.app.br/familias/create" class="btn btn-primary"><i class="fa fa-plus"></i> Novo cadastro</a>
                <a href="https://cmd.databrasil.app.br/familias" class="btn btn-secondary"><i class="fa fa-list"></i> Ver cadastros</a>
            </div>
        </div>
    </div> --}}
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 tit-sep">{{__('Painel')}}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{url('/')}}">Site</a></li>
                <li class="breadcrumb-item active">{{__('Painel Admin')}}</li>
            </ol>
        </div>
    </div>
    @if (isset($config['card_top']) && is_array($config['card_top']))
        <div class="row">
            @foreach ($config['card_top'] as $ck=>$cardv)
                <div class="col-lg-3 col-6">
                    <div class="small-box {{$cardv['color']}}">
                        <div class="inner">
                            <h3>{{$cardv['value']}}</h3>
                            <p>{{$cardv['label']}}</p>
                        </div>
                        <div class="icon">
                            <i class="{{$cardv['icon']}}"></i>
                        </div>
                        <a href="{{$cardv['link']}}" {{@$cardv['event']}} class="small-box-footer">Saiba mais <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            @endforeach

        </div>
    @endif

    {{-- <div class="row mb-2">
        @include('admin.leilao.lista_leilao_terminado')
        @include('admin.blacklist.cardpainel')
    </div> --}}


  </div>
  <script>
    // function buscaNp(){
    //     document.getElementById('list-finalizados_filter').querySelector('input[type="search"]').innerHTML = 'Aguardando';
    // }
  </script>
@stop

@section('css')
    @include('qlib.csslib')
    <style>
        .tit-sep{
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
@stop

@section('js')
    @include('qlib.jslib')
    {{-- @include('mapas.jslib') --}}
@stop
