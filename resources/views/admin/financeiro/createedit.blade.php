@extends('adminlte::page')
@include('admin.title')
@section('content_header')
<h3>{!!$titulo!!}</h3>
@stop
@php
    $tam_col1 = isset($config['tam_col1'])?$config['tam_col1'] : 'col-md-8';
    $tam_col2 = isset($config['tam_col2'])?$config['tam_col2'] : 'col-md-4';
    $ac = isset($config['ac'])?$config['ac'] : 'cad';
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

    <div class="{{$tam_col2}}">
        @if ($ac=='alt')
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">{{__('Receitas mensais')}}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12" id="painel-lancamento">
                                    <button type="button" class="btn btn-primary"
                                    action="{{route('receitas.store')}}"
                                    label="{{__('de receita mensal')}}"
                                    id_form="frm-cad-receita-mensal"
                                    onclick="financeiro_create_receita_mensal(this);" campos-mensais="{{$campos_mensais_code}}"  title=" {{__('Lançamento de nova receita mensal')}}"> {{__('Nova receita')}} </button>
                                </div>
                                <div class="col-12" id="list-mensais">

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (isset($value['token']) && @$config['arquivos'])
        <div class="row">
            <div class="col-12">
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
        </div>
        @endif
    </div>
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
    <script src="{{url('/js/financeiro.js')}}"></script>
    <script type="text/javascript">
        function alvoDescricaoReceita(dados){
            if(dados.post_name){
                $('[name="numero"]').val(dados.post_name);
                console.log(dados.post_name);
            }else{
                $('[name="numero"]').val('');
            }

        }
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
    {{-- Inclusaão das funções js --}}
    {{-- @include('admin.financeiro.receitas_mensais') --}}
    @include('qlib.js_submit')
@stop
