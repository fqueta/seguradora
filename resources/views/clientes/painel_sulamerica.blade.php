@php
    $ac = isset($ac) ? $ac : 'cad';
    $dados = isset($dados) ? $dados : false;
    $numOperacao = isset($dados['numOperacao']) ? $dados['numOperacao'] : false;
    $status_contrato = isset($dados['status_contrato']) ? $dados['status_contrato'] : false;
    $id = isset($dados['dados']['id']) ? $dados['dados']['id'] : false;
    $token = isset($dados['dados']['token']) ? $dados['dados']['token'] : false;
    // dd($dados);
@endphp
{{-- @if ($ac=='alt') --}}
<div class="row mb-4 ml-0 mr-0 ">
    @if ($status_contrato == 'Aprovado' || $status_contrato == 'aprovado')
            <div class="col-md-12 text-right">
                <button type="button" title="{{__('Cantelar o contrato na sulamerica')}}" data-operacao="{{$numOperacao}}" onclick="cancelarSulamerica('{{$token}}','{{$id}}',this)" class="btn btn-outline-danger">
                   <i class="fa fa-ban"></i> {{__('Cancelar sulamerica')}}
                </button>
            </div>
    @elseif($status_contrato == 'Cancelado' || $status_contrato == 'cancelado')
        @if ($dados)

            <div class="col-md-12 text-right">
                <button title="{{__('Reativar cadastro')}}" onclick="reativar_cadastro('{{$token}}','{{ URL::full() }}')" type="button" class="btn btn-outline-secondary ml-1"><i class="fa fa-recycle"></i> {{ __('Reativar sulamerica') }}</button>
            </div>

        @endif
    @endif
</div>
