@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@section('content')
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="col-md-8">
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
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                @if ($config['sec']=='orcamentos')
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
                            'pasta'=>'posts/'.date('Y').'/'.date('m'),
                            'token_produto'=>$value['token'],
                            'tab'=>'posts',
                            'listFiles'=>@$listFiles,
                            'routa'=>@$config['route'],
                            'arquivos'=>@$config['arquivos'],
                        ])}}
                    </div>
                </div>
                @else
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
    @if (App\Qlib\Qlib::qoption('editor_padrao')=='laraberg')
        <script src="https://unpkg.com/react@16.8.6/umd/react.production.min.js"></script>
        <script src="https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js"></script>
        <script src="{{ asset('vendor/laraberg/js/laraberg.js') }}"></script>
        <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    @endif
    @if(App\Qlib\Qlib::qoption('i_wp') && App\Qlib\Qlib::qoption('i_wp')!='s')
        <script>
            var lfm = function(id, type, options) {
            let button = document.getElementById(id);

            button.addEventListener('click', function () {
                var route_prefix = (options && options.prefix) ? options.prefix : '/laravel-filemanager';
                var target_input = document.getElementById(button.getAttribute('data-input'));
                var target_preview = document.getElementById(button.getAttribute('data-preview'));

                window.open(route_prefix + '?type=' + (options.type?options.type:'image') || 'file', 'FileManager', 'width=900,height=600');
                window.SetUrl = function (items) {
                var file_path = items.map(function (item) {
                    return item.url;
                }).join(',');

                // set the value of the desired input to image url
                target_input.value = file_path;
                target_input.dispatchEvent(new Event('change'));

                // clear previous preview
                target_preview.innerHtml = '';

                // set or change the preview image src
                items.forEach(function (item) {
                    let img = document.createElement('img')
                    img.setAttribute('style', 'height: 5rem')
                    img.setAttribute('src', item.thumb_url)
                    target_preview.appendChild(img);
                });

                // trigger change event
                target_preview.dispatchEvent(new Event('change'));
                };
                //console.log('aqui');
            });
        };
        var route_prefix = "/filemanager";
        lfm('lfm', 'image', {prefix: route_prefix});
        </script>
    @endif
    <script type="text/javascript">
        $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
        });
    </script>

    @include($config['view'].'.js_submit')
@stop
