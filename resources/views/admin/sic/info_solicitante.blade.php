<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">{{__('Dados do solicitante')}}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">

            @if (isset($config['info_solicitante']) && is_array($config['info_solicitante']))
                @foreach ($config['info_solicitante'] as $ks=>$vs)
                    @php
                        //dd($config);
                        $tam = 12;
                        if($ks=='email' || $ks=='celular'){
                            $tam=6;
                        }
                    @endphp
                    {{App\Qlib\Qlib::qShow([
                        'type'=>'text',
                        'campo'=>$ks,
                        'label'=>ucwords($ks),
                        'placeholder'=>'',
                        'ac'=>'alt',
                        'value'=>$vs,
                        'tam'=>$tam,
                        'event'=>'',
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

                @endforeach
            @endif
        </div>
    </div>
    @if(isset($config['info_solicitante']['id']) && !empty($config['info_solicitante']['id']))
    <div class="card-footer text-right">
        <a href="{{route('users.show',['id'=>$config['info_solicitante']['id']])}}?redirect={{App\Qlib\Qlib::urlAtual()}}" class="btn btn-outline-secondary" title="{{__('Detalhes do Solicitante')}}">Detalhes <i class="fas fa-arrow-right"></i></a>
    </div>
    @endif
</div>
