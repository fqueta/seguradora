@extends('adminlte::page')

@section('title')
{{$titulo}} - {{config('app.name')}} {{config('app.version')}}
@stop
@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@section('content')
<div class="row">
    <div class="col-md-12 mens">
        {{ App\Qlib\Qlib::formatMensagem( $_GET) }}
    </div>
    <div class="col-md-12">
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
    <!--
    <div class="col-md-4">
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
                App\Qlib\Qlib::gerUploadAquivos([
                    'pasta'=>'bairros/'.date('Y').'/'.date('m'),
                    'token_produto'=>$value['token'],
                    'tab'=>'bairros',
                    'listFiles'=>@$listFiles,
                ])}}
            </div>
        </div>
    </div>-->
</div>

@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
          $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('#inp-password').val('');
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-cnpj]').inputmask('99.999.999/9999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
          });
    </script>
    @include('qlib.js_submit')
@stop
