@php
    $card_title = isset($card_title)?$card_title : 'Leilões Ganhos';
@endphp
<div class="card mb-3">
    <div class="card-header">
        <h5>{{__($card_title)}}</h5>
    </div>
    <div class="card-body">
        @if(isset($ganhos) && is_array($ganhos))
            <table class="table table-striped">
                <head>
                    <tr>
                        <th>{{__('Leilão')}}</th>
                        <th>{{__('Termino')}}</th>
                        <th>{{__('Valor')}}</th>
                        <th class="text-center">{{__('Status')}}</th>
                        <th class="text-end">{{__('Ação')}}</th>
                    </tr>
                </head>
                <tbody>
                    @foreach ( $ganhos as $k=>$v)

                        <tr>
                            <td>{{$v['post_title']}}</td>
                            <td>{{@$v['term']['html']}}</td>
                            <td>{{App\Qlib\Qlib::valor_moeda(@$v['venc']['valor_lance'])}}</td>
                            <td class="text-center">
                                @php echo @$v['situacao_pagamento'] @endphp
                            </td>
                            @if (isset($v['status_pago']) && ($v['status_pago']=='s' || $v['status_pago']=='a'))
                                <td class="">
                                    @php
                                        echo (new App\Http\Controllers\PaymentController) -> get_info_pagamento($v['ID'])
                                    @endphp
                                </td>
                            @else
                                <td class="text-end">
                                        @php
                                        $acao = '<a href="'.@$v['link_pagamento'].'" class="btn btn-success">'.__('Pagar').'</a>';
                                        echo $acao;
                                        @endphp
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    {{-- <div class="card-footer text-muted">
        Footer
    </div> --}}
</div>
