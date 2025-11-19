@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@php
     $processo_cancelamento = $dados['dados']['processo_cancelamento'] ?? [];
@endphp
@section('content')
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{__('Informações')}}</h3>
                <div class="card-tools d-print-none">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::show([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}}

                {{--
                    PT/EN: Include do histórico de contrato (view admin) dentro do card.
                    Português: Prepara $contrato e $events com base no token do registro atual
                    e inclui a view `admin.contratos.history` na posição solicitada.

                    English: Prepare $contrato and $events from the current record token
                    and include `admin.contratos.history` view at the requested position.
                --}}
                @php
                    // Resolve token do contrato a partir do registro do cliente
                    $tokenContrato = $value['token']
                        ?? ($value['dados']['token'] ?? null);

                    $contrato = null;
                    $events = collect();

                    if ($tokenContrato) {
                        $contrato = \App\Models\Contrato::where('token', $tokenContrato)->first();
                        if ($contrato) {
                            // Filtra eventos de integracao_sulamerica e reativacao
                            $events = \App\Models\ContractEvent::where('contrato_id', $contrato->id)
                                // ->whereIn('event_type', ['integracao_sulamerica','reativacao'])
                                // ->whereIn('event_type', ['integracao_sulamerica','reativacao','cancelamento_end'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                        }
                    }
                @endphp
                @include('admin.contratos.history', ['contrato' => $contrato, 'events' => $events])
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
                    <div class="col-md-12 text-right">
                            <button title="{{__('Reativar cadastro')}}" onclick="reativar_cadastro('{{$token}}','{{ URL::full() }}')" type="button" class="btn btn-outline-secondary ml-1"><i class="fa fa-recycle"></i> {{ __('Reativar sulamerica') }}</button>
                        </div>

                @endif
            </div>

            {{-- @include('qlib.show_files') --}}
        </div>
    </div>
</div>

@stop

@section('css')
    @include('qlib.csslib')
    <style>
        .div-salvar{
            padding: 0px
        }
    </style>
@stop

@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
          $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('#inp-cpf,#inp-cpf_conjuge').inputmask('999.999.999-99');
          });
    </script>
@stop

