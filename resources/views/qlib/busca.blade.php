<!-- Modal -->

<div class="col-md-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="card-title">{{__('Pesquisar cadastros')}}</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                  <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!--<form action="" method="GET">-->
                <div class="row">
                    @if (isset($campos_tabela))
                        @foreach ($campos_tabela as $kbu=>$vbu)
                            @if ($vbu['active'])
                            @php
                                if($vbu['type']!='text' && $kbu=='id'){
                                    $vbu['type'] = 'text';
                                }
                                if($vbu['type']=='hidden_text'){
                                    $vbu['type'] = 'text';
                                }
                                if($kbu!='obs')
                                    $vbu['tam'] = 3;
                                $cp_busca = isset($vbu['cp_busca'])?$vbu['cp_busca']:$kbu;
                                // Compute current value from GET using cp_busca for nested fields
                                $val_busc = isset($_GET['filter'][$kbu]) ? $_GET['filter'][$kbu] : '';
                                if(isset($vbu['cp_busca'])){
                                    $parts = explode('][', $cp_busca);
                                    if(count($parts) > 1){
                                        $tmp = isset($_GET['filter']) ? $_GET['filter'] : [];
                                        foreach($parts as $p){
                                            $pp = trim($p,'[]');
                                            if(is_array($tmp) && isset($tmp[$pp])){
                                                $tmp = $tmp[$pp];
                                            }else{
                                                $tmp = '';
                                                break;
                                            }
                                        }
                                        if(!is_array($tmp)){
                                            $val_busc = $tmp;
                                        }
                                    }else{
                                        if(isset($_GET['filter'][$cp_busca])){
                                            $val_busc = $_GET['filter'][$cp_busca];
                                        }
                                    }
                                }
                            @endphp
                                {{App\Qlib\Qlib::qForm([
                                    'type'=>isset($vbu['type'])?$vbu['type']:'text',
                                    'campo'=>'filter['.$cp_busca.']',
                                    'placeholder'=>isset($vbu['placeholder'])?$vbu['placeholder']:'',
                                    'label'=>$vbu['label'],
                                    'ac'=>'alt',
                                    'value'=>$val_busc,
                                    'tam'=>isset($vbu['tam'])?$vbu['tam']:'3',
                                    'class_div'=>$vbu['exibe_busca'],
                                    'event'=>isset($vbu['event'])?$vbu['event']:'',
                                    'arr_opc'=>isset($vbu['arr_opc'])?$vbu['arr_opc']:'',
                                    'label_option_select'=>'Todas',
                                ])}}
                            @endif

                        @endforeach
                    @else
                    {{App\Qlib\Qlib::qForm([
                        'type'=>'text',
                        'campo'=>'filter[loteamento]',
                        'placeholder'=>'Loteamento',
                        'label'=>'Loteamento',
                        'ac'=>'alt',
                        'value'=>@$_GET['filter']['loteamento'],
                        'tam'=>'4',
                        'event'=>'',
                    ])}}
                    {{App\Qlib\Qlib::qForm([
                        'type'=>'text',
                        'campo'=>'filter[area_alvo]',
                        'placeholder'=>'Informe Área',
                        'label'=>'Área Alvo',
                        'ac'=>'alt',
                        'value'=>@$_GET['filter']['area_alvo'],
                        'tam'=>'2',
                        'event'=>'',
                        ])}}
                    @endif
                    <div class="col-md-12">
                        <div class="btn-group">
                            <button class="btn btn-primary" type="submit"> <i class="fas fa-search"></i> Localizar</button>
                            <a href=" {{route($routa.'.index')}} " class="btn btn-default" title="Limpar Filtros" type="button"> <i class="fas fa-times"></i> Limpar</a>
                            @include($view.'.dropdow_actions')
                        </div>
                    </div>
                </div>
            <!--</form>-->
        </div>
    </div>
</div>
