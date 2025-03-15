@php
    //PaymentController->agradecimento();
    //$dl = dados do leilão
    //$ul = dados do ultimo lance ou seja do ganhador leilão
    //$dc = dados do contrato
    //$dt = dados do Terminio do leilao
    $thumbnail      = isset($dl['thumbnail'])?$dl['thumbnail']:false;
    $leilao         = isset($dl['post_title'])?$dl['post_title']:false;
    $contrato       = isset($dc['post_title'])?$dc['post_title']:false;
    $valor          = isset($ul['valor_lance'])?$ul['valor_lance']:@$valor;
    $nome_cliente   = isset($ul['nome'])?$ul['nome']:false;
    $data_lance     = isset($ul['created_at'])?$ul['created_at']:false;
    $termino        = isset($dt['html'])?$dt['html']:false;
    $valor_pix = '<span>'.App\Qlib\Qlib::valor_moeda($valor,'R$ ').'</span><input type="hidden" class="total-pix" name="compra[valor]" value="'.$valor.'" />';
	$valor_boleto = '<span>'.App\Qlib\Qlib::valor_moeda($valor,'R$ ').'</span><input type="hidden" class="total-boleto" name="compra[valor]" value="'.$valor.'" />';
@endphp

@if(isset($dl))
<div class="row mt-4">
    <div class="col-md-12 mens">
        {!!@$mens!!}
    </div>
    @if (($status==200 || $status==201) && $mensagem)
    <div class="card">
        <div class="card-header">
            <h5>
                {{__('Resumo do pagamento')}}
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-3 pr-0">
                    <img src="{{$thumbnail}}" width="100%" alt="{{$leilao}}">
                </div>
                <div class="col-9">
                    <h3>{{$leilao}}</h3>
                    <h5>{{$contrato}}</h5>
                    <h3>{{App\Qlib\Qlib::valor_moeda($valor,'R$ ')}}</h3>
                    @if(isset($ul['id']) && !empty($ul['id']))
                    <ul>
                        <li>
                            <small><b>Termino:</b> {{$termino}}</small>
                        </li>
                        <li>
                            <small><b>Ganhador:</b> {{$nome_cliente}}</small>
                        </li>
                        <li>
                            <small><b>Data do Lance:</b> {{App\Qlib\Qlib::dataExibe($data_lance)}}</small>
                        </li>
                    </ul>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    @php
                        echo $mensagem;
                    @endphp
                </div>
            </div>
            @if(isset($arr_info_pagamento) && is_array($arr_info_pagamento))
                @include('site.leiloes.payment.info_pagamento',[
                    'as'=>$arr_info_pagamento
                ])
            @endif
        </div>
    </div>



    @endif
</div>
@endif
