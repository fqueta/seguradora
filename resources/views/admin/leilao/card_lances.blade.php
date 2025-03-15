
<div class="row">
    <div class="card card-primary card-outline" style="width: 100%">
        <div class="card-header">
            <h3 class="card-title">{{__('Histórico de Lances')}}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                  <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body card-h overflow-auto mw-100 d-flex">
            <div class="row">
                @if(isset($dados['situacao']) && $dados['situacao']=='f')
                    <div class="col-12">
                        @if(isset($dados['ranking']['ganhadores']) && is_array($dados['ranking']['ganhadores']))
                            <table class="ranking table mt-0 mb-0">
                                <thead>
                                    <tr>
                                        <th colspan="4">
                                            <h6> {{__('Ranking de ganhadores')}} </h6>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dados['ranking']['ganhadores'] as $kg=>$vg )
                                        @php
                                            $n = explode(' ',@$vg['name']);
                                            if(isset($vg['config']['ddi']) && !empty($vg['config']['ddi']) && isset($vg['config']['telefonezap']) && !empty($vg['config']['telefonezap'])){
                                                // abrir janela para o envio da mensagem
                                                $link_contato = '#';
                                            }else{
                                                if(isset($vg['author']) && !empty($vg['author'])){
                                                    $link_contato = route('users.edit',['id'=>$vg['author']]).'?redirect='.App\Qlib\Qlib::UrlAtual();
                                                }else{
                                                    $link_contato = '#';
                                                }
                                            }
                                        @endphp
                                        <tr class="text-{{@$vg['color']}}">
                                            <td class="numero">
                                                {{$kg}}°
                                            </td>
                                            <td class="name">
                                                @if (isset($vg['author']) && $vg['author']>1)
                                                    <a class="underline" title="{{__('Ver detalhes')}}" href="{{route('users.show',['id'=>$vg['author']])}}?redirect={{App\Qlib\Qlib::UrlAtual()}}">
                                                        {{@$n[0]}}
                                                    </a>
                                                @else
                                                    {{@$n[0]}}
                                                @endif
                                            </td>
                                            <td class="valor text-right">
                                                {{App\Qlib\Qlib::valor_moeda(@$vg['valor_lance'])}}
                                            </td>
                                            <td>
                                                <div class="dropdown show">
                                                    <a class="btn btn-default dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </a>

                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                      <button data-valor="{{@$vg['valor_lance']}}" data-nome_leilao="{{@$dados['post_title']}}" class="dropdown-item" dta="{{App\Qlib\Qlib::encodeArray(@$vg)}}" type="button" href="{{$link_contato}}" onclick="contatar_ganhador(this)"><i class="fab fa-whatsapp"></i> Contatar</button>
                                                      @if($kg>1)
                                                          <a class="dropdown-item" href="javascript:void(0)" onclick="tornar_vencedor('{{$vg['id']}}')"> <i class="fas fa-gavel"></i> Marcar como ganhador</a>
                                                      @endif
                                                      {{-- <a class="dropdown-item" href="#">Something else here</a> --}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
                <div class="col-12">
                    <a href="javascript:void(0);" class="underline" data-toggle="modal" data-target="#modalLances" id="btn-ver_lances">{{__('Todos Lances')}} ({{$dados['total_lances']}}) </a>
                </div>
                <div class="col-12">
                    @include('qlib.partes_html',['config'=>[
                        'parte'=>'modal',
                        'id'=>'modalLances',
                        'title'=>'Lances',
                        'tam'=>'modal-lg',
                        'bt_acao'=>false,
                        'botao_fechar'=>false,
                        'include'=>'site.leiloes.lances.list_lances',
                    ]])
                </div>
            </div>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
{{-- <script>
    $(function(){
        $('#btn-ver_lances').on('click', function(){
            $('#modalLances').modal('show');
        });

    });
</script> --}}


