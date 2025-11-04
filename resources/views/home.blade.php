@extends('adminlte::page')

@section('title', 'Data Brasil - Painel')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Painel</h1>
    </div><!-- /.col -->
    <div class="col-sm-6 text-right">
        <div class="btn-group" role="group" aria-label="actions">
            <a href="{{route('clientes.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> {{__('Novo cliente')}}</a>
            <a href="{{route('clientes.index')}}" class="btn btn-secondary"><i class="fa fa-list"></i> {{__('Todos clientes')}}</a>
            {{-- <a href="{{route('relatorios.social')}}" class="btn btn-dark"><i class="fa fa-chart-bar"></i> Ver relatórios</a> --}}
        </div>
    </div><!-- /.col -->
</div>
@stop

@section('content')
<!--<p>Welcome to this beautiful admin panel.</p>-->
@can('ler','relatorios')
<div class="row card-top">
    @if (isset($config['c_posts']['cards_home']))
    @foreach ($config['c_posts']['cards_home'] as $k=>$v)
    <div class="col-lg-{{$v['lg']}} col-{{$v['xs']}}">
                <!-- small box -->
                <div class="small-box bg-{{$v['color']}}">
                  <div class="inner">
                    <h3>{{$v['valor']}}</h3>

                    <p>{{$v['label']}}</p>
                  </div>
                  <div class="icon">
                    <i class="{{$v['icon']}}"></i>
                  </div>
                  <a href="{{$v['href']}}" class="small-box-footer">Visualizar <i class="fas fa-arrow-circle-right"></i></a>
                </div>
              </div>
            @endforeach
        @endif
    </div>

    <div class="row mb-5">
        @if (isset($config['c_posts']['progresso']))
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <strong>Progresso dos cadastros</strong>
                </div>
                <div class="card-body">
                    @foreach ($config['c_posts']['progresso'] as $k=>$v)
                        <div class="progress-group">
                            {{$v['label']}}
                            <span class="float-right"><b>{{$v['total']}}</b>/{{$v['geral']}}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar {{$v['color']}}" style="width: {{$v['porcento']}}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-7">
            {{-- @if (isset($config['mapa']['config']))
                {!! App\Http\Controllers\MapasController::exibeMapas($config['mapa']['config']) !!}
            @endif --}}
        </div>
    </div>
    @else
    <div class="col-md-12">

        <h4>Seja bem vindo ao painel caso não tenha a permissão de acesso, entre em contato com o suporte</h4>
    </div>

    @endcan


  </div>
@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
@stop
