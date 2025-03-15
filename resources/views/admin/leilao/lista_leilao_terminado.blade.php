@php
    $redirect = '?redirect='.url('/admin');
@endphp
<div class="col-md-12">
    <div class="card">
        <div class="card-header  border-transparent">
            <h3 class="card-title">
                {{__('Lista de leilões finalizados e ganhadores')}}
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="list-finalizados" class="table table-striped dataTable">
                    <thead>
                        <tr>
                            <th>{{__('ID')}}</th>
                            <th>{{__('Leilão')}}</th>
                            <th>{{__('Ganhador')}}</th>
                            <th>{{__('Termino')}}</th>
                            <th>{{__('Valor')}}</th>
                            <th class="text-center">{{__('Pagamento')}}</th>
                            <th class="text-center">{{__('...')}}</th>
                        </tr>
                    </thead>
                    @if (isset($config['lista_leilao_terminado']) && is_array($config['lista_leilao_terminado']))

                        @foreach ($config['lista_leilao_terminado'] as $k=>$v)
                            <tr>
                                <td>
                                    <a href="{{$v['link_leilao_front'].$redirect}}" title="{{$v['post_title']}}" class="underline" data-togle="tooltip" title="{{__('Ver no painel Admin')}}">
                                        {{$v['ID']}}
                                    </a>
                                </td>
                                @if(isset($v['link_leilao_front']) && $v['link_leilao_front'])
                                <td class="title-table-home-leilao">
                                    <a href="{{url('/admin/leiloes_adm/'.$v['ID'].'?redirect=').url('/admin')}}" class="underline" rel="">
                                        {{$v['post_title']}}
                                    </a>
                                </td>
                                @else
                                    <td>
                                        {{$v['post_title']}}
                                    </td>
                                @endif
                                <td>
                                    @if (isset($v['venc']['author']) && !empty($v['venc']['author']))
                                        <a class="underline" href="{{route('users.show',['id'=>$v['venc']['author']]).$redirect}}" rel="">
                                            {{@$v['venc']['nome']}}
                                        </a>
                                    @else
                                        <span class="text-danger"> {{__('Sem lances')}} </span>
                                    @endif
                                </td>
                                <td>{{@$v['term']['html']}}</td>
                                <td>{{App\Qlib\Qlib::valor_moeda(@$v['venc']['valor_lance'])}}</td>
                                <td class="text-center">
                                    {!!@$v['situacao_pagamento']!!}
                                </td>
                                <td class="text-right">
                                    <a href="{{route('leiloes_adm.show',['id'=>$v['ID']])}}?redirect={{url('/admin')}}" title=" {{__('Visualizar')}} " class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a>
                                    @if (isset($v['status_pago']) && ($v['status_pago']=='s' || $v['status_pago']=='a'))
                                        @php
                                            echo (new App\Http\Controllers\PaymentController) -> get_info_pagamento($v['ID'])
                                        @endphp
                                    @else
                                        @if (@$v['situacao']=='r')
                                            <a href="{{route('leiloes_adm.edit',['id'=>$v['ID']])}}?redirect={{url('/admin')}}" title=" {{__('Editar')}} " class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> {{__('Editar')}} </a>
                                        @else
                                            <button title=" {{__('Reciclar o leilão')}} " onclick="reciclar('{{$v['ID']}}');" class="btn btn-info btn-sm"><i class="fas fa-recycle"></i></button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
