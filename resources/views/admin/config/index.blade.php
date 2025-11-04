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

    @if (isset($value['token']) && @$config['arquivos'])
        <div class="{{$tam_col2}}">
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
        </div>
    @endif
</div>
@stop

@section('css')
    @include('qlib.csslib')
@stop
{{-- <script>
    window.onload = ()=>{
    }
</script> --}}
@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
        $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-cnpj]').inputmask('99.999.999/9999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
            list_arquivos('#dados-lista-files');
        });

    </script>
    @include('qlib.js_submit')
@stop
