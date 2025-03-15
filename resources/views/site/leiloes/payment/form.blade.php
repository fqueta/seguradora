@php
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
    $termino       = isset($dt['html'])?$dt['html']:false;
    // dd($dt);
    $valor_pix = '<span>'.App\Qlib\Qlib::valor_moeda($valor,'R$ ').'</span><input type="hidden" class="total-pix" name="compra[valor]" value="'.$valor.'" />';
	$valor_boleto = '<span>'.App\Qlib\Qlib::valor_moeda($valor,'R$ ').'</span><input type="hidden" class="total-boleto" name="compra[valor]" value="'.$valor.'" />';
    $type = $type?$type:'01';
@endphp

@if(isset($dl))
<div class="row mt-4">
    <div class="col-md-12 mens">
        {!!@$mens!!}
    </div>
    @if ($status==200)
    <div class="card">
        <div class="card-header">
            <h5>
                {{__('Pagamentos')}}
            </h5>
        </div>
        <div class="card-body">
            <style>
                @media (min-width: 992px){
                    .cont-pag-v2 {
                        max-width: 960px;
                        box-shadow: 0 1px 5px 0 rgb(0 0 0 / 20%), 0 2px 2px 0 rgb(0 0 0 / 14%), 0 3px 1px -2px rgb(0 0 0 / 12%);
                        background: #fff;
                        overflow: hidden;
                        margin-top: 25px;
                        margin-bottom: 5px;
                        padding-bottom: 25px;
                    }
                }
                .cont-pag-v2 h2 {
                    font-size: 1.2em;
                    font-weight: bold;
                }
                .met-pay [type="radio"]{
                    display: none;
                }
            </style>
            <div class="container cont-pag-v2 mb-5 mt-5">
                {{-- <div class="row mb-3">
                    <div class="col-12">
                        <img src="{{$thumbnail}}" width="100%" alt="{{$dl['post_title']}}-b">
                    </div>
                </div> --}}
                <div class="row mb-3">
                    <div class="col-3 pr-0">
                        <img src="{{$thumbnail}}" width="100%" alt="{{$leilao}}">
                    </div>
                    <div class="col-9">
                        <h3>{{$leilao}}</h3>
                        <h5>{{$contrato}}</h5>
                        <h3>{{App\Qlib\Qlib::valor_moeda($valor,'R$ ')}}</h3>
                        @if($type=='01')
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
                {{-- <?=$dadosCliente?> --}}
                <form id="frm-pag-v2" class="frm-pag-v2" action="" method="post">
                    @csrf
                    <input type="hidden" name="compra[token]" value="{{$dl['token']}}"/>
                    <div class="row ml-0 mr-0 mb-3">
                        {{-- <?=$formCampos?> --}}
                    </div>
                        <div class="col-12 mb-3 px-0">
                            <div class="w-100 btn-group btn-group-toggle met-pay" data-toggle="buttons">
                                <label class="btn btn-outline-primary btn-lg active col-6" id="lb-card">
                                    <input type="radio" name="compra[forma_pagamento]" value="cred_card" checked> <i class="fa fa-credit-card    "></i> Cartão de crédito
                                </label>
                                <label class="btn btn-outline-primary btn-lg col-6" id="lb-pix">
                                    <input type="radio" name="compra[forma_pagamento]" value="pix"> <svg width="23px" height="22" viewBox="0 0 22 23" preserveAspectRatio="xMidYMin" provider="" class="icon-pix pix-gray"  data-v-d2a71a6c=""><path fill-rule="evenodd" clip-rule="evenodd" d="M5.19223 5.24323C6.06969 5.24323 6.89487 5.58498 7.51525 6.20516L10.8818 9.57225C11.1243 9.8147 11.5202 9.81575 11.7633 9.57193L15.1175 6.21736C15.738 5.59718 16.5632 5.25554 17.4407 5.25554H17.8447L13.5842 0.995107C12.2574 -0.331702 10.1063 -0.331702 8.77948 0.995107L4.53135 5.24323H5.19223ZM17.4406 17.108C16.5632 17.108 15.738 16.7664 15.1176 16.1462L11.7632 12.792C11.5278 12.5558 11.1173 12.5565 10.8819 12.792L7.51531 16.1585C6.89482 16.7786 6.06964 17.1202 5.19219 17.1202H4.5312L8.77943 21.3686C10.1062 22.6953 12.2574 22.6953 13.5842 21.3686L17.8447 17.108H17.4406ZM18.794 6.20484L21.3686 8.77947C22.6954 10.1062 22.6954 12.2573 21.3686 13.5842L18.7941 16.1587C18.7373 16.1359 18.6761 16.1218 18.6112 16.1218H17.4407C16.8354 16.1218 16.243 15.8764 15.8154 15.4484L12.4611 12.0945C11.8532 11.4859 10.7925 11.4862 10.184 12.0942L6.81744 15.4607C6.38976 15.8886 5.79746 16.134 5.19222 16.134H3.75286C3.69154 16.134 3.634 16.1486 3.57983 16.169L0.995108 13.5842C-0.331703 12.2573 -0.331703 10.1062 0.995108 8.77947L3.57994 6.19464C3.63411 6.21504 3.69154 6.22956 3.75286 6.22956H5.19222C5.79746 6.22956 6.38976 6.47496 6.81744 6.90285L10.1843 10.2697C10.4982 10.5833 10.9103 10.7404 11.3227 10.7404C11.7349 10.7404 12.1473 10.5833 12.4611 10.2694L15.8154 6.91505C16.243 6.48716 16.8354 6.24176 17.4407 6.24176H18.6112C18.676 6.24176 18.7373 6.22756 18.794 6.20484Z" ></path></svg> PIX
                                </label>
                                {{-- <label class="btn btn-outline-primary btn-lg col-4" id="lb-boleto">
                                    <input type="radio" name="compra[forma_pagamento]" value="boleto"> <i class="fa fa-barcode" aria-hidden="true"></i> Boleto
                                </label> --}}
                                <!-- <label class="btn btn-outline-primary btn-lg col-4  ">
                                    <input type="radio" name="compra[forma_pagamento]" value="conta"> <i class="fa fa-user" aria-hidden="true"></i> Conta
                                </label>						 -->
                            </div>

                        </div>
                        <div id="c-cred_card" class="row c-pag mr-0 ml-0 mb-3">
                            <div class="col-12">
                                {!!@$form_credit_cart!!}
                            </div>
                        </div>
                        <div id="c-pix" style="display:none" class="row c-pag mr-0 ml-0 mb-3">
                            <div class="col-12">
                                @include('site.leiloes.payment.form_pix',['valor'=>$valor_pix,'nome_contrato'=>$contrato])
                            </div>
                        </div>
                        {{-- <div id="c-boleto" style="display:none" class="row c-pag mr-0 ml-0 mb-3">
                            <div class="col-12">
                                @include('site.leiloes.payment.form_boleto',['valor'=>$valor_boleto,'nome_contrato'=>$contrato])
                            </div>
                        </div> --}}
                        <div class="row mr-0 ml-0">
                            <div class="col-md-12 mens2"></div>
                            <div class="col-12 div-bt-submit">

                            </div>
                        </div>
                </form>
                <div id="c-conta" style="display:none" class="row c-pag mr-0 ml-0 mb-3">
                    <div class="col-12">
                        {tm_conta}
                    </div>
                </div>

            </div>
            <script>
                $(function(){
                    ecomerce_initPayment();
                });
            </script>
        </div>
    </div>



    @endif
</div>
@endif
