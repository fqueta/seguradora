<div class="row">
    <div class="col-md-12">{{__('Dados da fatura')}} - {{@$as['invoiceNumber']}}</div>
    <div class="col-md-12">
    </div>
</div>
<div class="row">
    <div class="col-md-6 ">
        <div class="row">
            <div class="col-12">
                <b>
                    {{__('Valor pago')}}
                </b>
                <span class="display-6 green text-success">
                    {{@$as['valor']}}
                </span>
            </div>
            @if(isset($as['installmentNumber']) && !empty($as['installmentNumber']))
            <div class="col-12">
                {{__('Parcelado')}} {{@$as['installmentNumber']}}x de {{$as['value']}}
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-6 ">
            <div class="label">{{__('Data de vencimento')}}:</div>
            <div class="text-secondary">
                {{@$as['vencimento']}}
            </div>
            <div class="label">{{__('Data de pagamento')}}:</div>
            <div class="text-secondary">
                {{@$as['pagamento']}}
            </div>
                <div class="label">{{__('Forma de pagamento')}}:</div>
            <div class="text-secondary">
                {{@$as['forma_pagamento']}}
            </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
            <div class="label">{{__('Descrição')}}</div>
            <div class="js-payment-checkout-collapsable-description adjust-text">
                <p class="pre-line"> {{@$as['description']}}</p>
            </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mt-2">
        <a href="{{@$as['invoiceUrl']}}" target="blank" class="btn btn-outline-primary">{{__('Detalhes da fatura')}}</a>
    </div>
    <div class="col-md-6 mt-2">
        <a href="{{@$as['transactionReceiptUrl']}}" target="blank" class="btn btn-outline-secondary">{{__('Comprovante')}}</a>
    </div>
</div>
