@php
    $ac = isset($ac) ? $ac : 'cad';
    $dados = isset($dados) ? $dados : false;
    $numOperacao = isset($dados['numOperacao']) ? $dados['numOperacao'] : false;
    $status_contrato = isset($dados['status_contrato']) ? $dados['status_contrato'] : false;
    $id = isset($dados['dados']['id']) ? $dados['dados']['id'] : false;
    $token = isset($dados['dados']['token']) ? $dados['dados']['token'] : false;
    // dd($dados);
    $processo_cancelamento = $dados['dados']['processo_cancelamento'] ?? [];
    // dd($processo_cancelamento);
    $status_local = false;
    if(!$status_contrato && isset($dados['dados']['config[status_contrato]'])){
        // $status_contrato = $dados['dados']['config[status_contrato]'];
        $status_local = $dados['dados']['config[status_contrato]'];

        // $status_contrato = 'Aprovado';
    }
    // dd($status_contrato,$status_local,$dados['dados']['config[status_contrato]']);
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
    @elseif($status_local == 'Aprovado' || $status_local == 'aprovado')
        @if ($dados)

            <div class="col-md-12 text-right">
                <span class="badge badge-danger">{{ __('Sem status sulamerica') }}</span>
                {{-- <button type="button" title="{{__('Cantelar o contrato localmente')}}" data-operacao="{{$numOperacao}}" onclick="cancelarSulamerica('{{$token}}','{{$id}}',this)" class="btn btn-outline-danger">
                   <i class="fa fa-ban"></i> {{__('Cancelar localmente')}}
                </button> --}}
            </div>

        @endif
    @endif
</div>
<div class="row mb-4 ml-0 mr-0 ">
    @if (isset($processo_cancelamento['data_cancelamento']))
        <div class="col-md-12 text-right">
            <h4 class="text-center bg-secondary">{{ __('Processo de cancelamento (Acompanhamento Local)') }}</h4><hr>
            <table class="table table-striped table-sm">
                <tr>
                    <th>{{ __('Data do cancelamento') }}</th>
                    <td>{{ $processo_cancelamento['data_cancelamento'] }}</td>
                </tr>
                <tr>
                    <th>{{ __('Canceldo Por') }}</th>
                    <td>{{ $processo_cancelamento['cancelado_por_nome'] }}</td>
                </tr>
            </table>
        </div>
    @endif
</div>
