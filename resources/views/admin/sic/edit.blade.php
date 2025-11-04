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
        @include('admin.sic.info_solicitante')

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Solicitação</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @php
                    // dd($campos_solicitacao);
                @endphp
                @if (isset($campos_solicitacao) && is_array($campos_solicitacao))
                    @foreach ($campos_solicitacao as $kc=>$vc)
                        @if($kc=='protocolo' || $kc=='mensagem')
                            {{App\Qlib\Qlib::qShow([
                                'type'=>@$vc['type'],
                                'campo'=>$kc,
                                'label'=>$vc['label'],
                                'placeholder'=>@$vc['placeholder'],
                                'ac'=>$config['ac'],
                                'value'=>isset($vc['value'])?$vc['value']: @$value[$kc],
                                'tam'=>@$vc['tam'],
                                'event'=>@$vc['event'],
                                'checked'=>@$value[$k],
                                'selected'=>@$vc['selected'],
                                'arr_opc'=>@$vc['arr_opc'],
                                'option_select'=>@$vc['option_select'],
                                'class'=>@$vc['class'],
                                'class_div'=>@$vc['class_div'],
                                'rows'=>@$vc['rows'],
                                'cols'=>@$vc['cols'],
                                'data_selector'=>@$vc['data_selector'],
                                'script'=>@$vc['script_show'],
                                'valor_padrao'=>@$vc['valor_padrao'],
                                'dados'=>@$vc['dados'],
                            ])}}
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Resposta</h3>
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
                {{App\Qlib\Qlib::gerUploadAquivos([
                    'pasta'=>$config['route'].'/'.date('Y').'/'.date('m'),
                    'token_produto'=>$value['token'],
                    'tab'=>$config['route'],
                    'listFiles'=>@$listFiles,
                    'routa'=>@$config['route'],
                    'url'=>@$config['url'],
                    'arquivos'=>@$config['arquivos'],
                    'typeN'=>@$config['typeN'],
                    'local'=>@$config['local'],
                ])}}
            </div>
        </div>
    </div>
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
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
        });

    </script>
    @include('qlib.js_submit')
@stop
