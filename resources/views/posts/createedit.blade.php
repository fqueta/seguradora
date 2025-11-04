@extends('adminlte::page')
@include('admin.title')
@section('content_header')
<h3>{!!$titulo!!}</h3>
@stop
@php
    $tam_col1 = isset($config['tam_col1'])?$config['tam_col1'] : 'col-md-8';
    $tam_col2 = isset($config['tam_col2'])?$config['tam_col2'] : 'col-md-4';
@endphp
@section('content')
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="{{$tam_col1}}">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informações</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::formulario([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}}
            </div>
        </div>
    </div>
    @if($config['ac']=='alt')
    <div class="{{$tam_col2}}">
        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{__('Imagem Destacada')}}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(App\Qlib\Qlib::qoption('i_wp')=='s')
                            {{App\Qlib\Qlib::gerUploadWp([
                                'pasta'=>$config['route'].'/'.date('Y').'/'.date('m'),
                                'id'=>@$config['id'],
                                'token_produto'=>$value['token'],
                                'tab'=>$config['route'],
                                'listFiles'=>@$listFiles,
                                'routa'=>@$config['route'],
                                'arquivos'=>@$config['arquivos'],
                                'typeN'=>@$config['typeN'],
                            ])}}

                        @else
                            @include('admin.media.painel_select_media',['ac'=>'view','value'=>$value])
                        @endif
                    </div>
                </div>
                @if (isset($value['token']) && @$config['arquivos'])
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Arquivos</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{App\Qlib\Qlib::gerUploadAquivos([
                                'pasta'=>$config['route'].'/'.date('Y').'/'.date('m'),
                                'token_produto'=>isset($value['token'])?$value['token']:false,
                                'tab'=>$config['route'],
                                'listFiles'=>@$listFiles,
                                'listFilesCode'=>@$listFilesCode,
                                'routa'=>@$config['route'],
                                'url'=>@$config['url'],
                                'arquivos'=>@$config['arquivos'],
                                'typeN'=>@$config['typeN'],
                            ])}}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        {{-- @if ($config['sec']!='decretos')
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{__('Categoria(s)')}}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                              <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{__('Galeria(s)')}}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                              <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>
        @endif --}}
    </div>
    @endif
</div>

@stop

@section('css')
@if (App\Qlib\Qlib::qoption('editor_padrao')=='laraberg')
    <link rel="stylesheet" href="{{asset('vendor/laraberg/css/laraberg.css')}}">
@endif
    <style>
        .interface-interface-skeleton__sidebar{
            display: none;
        }
    </style>
    @include('qlib.csslib')
    @stop

    @section('js')
    @include('qlib.jslib')
    {{-- @if (App\Qlib\Qlib::qoption('editor_padrao')=='laraberg')
        <script src="https://unpkg.com/react@16.8.6/umd/react.production.min.js"></script>
        <script src="https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js"></script>
        <script src="{{ asset('vendor/laraberg/js/laraberg.js') }}"></script>
        <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    @endif --}}
    <script type="text/javascript">
        $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
            list_arquivos('#dados-lista-files');
        });
    </script>
    @include($config['view'].'.js_submit')
@stop
