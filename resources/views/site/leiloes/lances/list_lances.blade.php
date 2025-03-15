@php
    $seg1 = request()->segment(1); //link da página em questão
    $urllistaTodos = App\Qlib\Qlib::get_slug_post_by_id(3); //link da pagina para listar todos o lances do cliente.
    // dd($lances_vencendo);
@endphp
@if ($seg1==$urllistaTodos)
<div class="col-md-12 mt-5">
    <span class="text-muted"> {{__('São exibidos seus lances em leilões ativos ou em leilões que foram finalizados nos últimos 7 dias')}} </span>
</div>
<div class="col-md-12 mb-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                {{__('Lances Vencendo Leilão')}}
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @if(isset($lances_vencendo) && is_array($lances_vencendo))
                {{-- {{dd($lances_vencendo)}} --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th width="30%">{{__('Data')}}</th>
                            <th class="text-center">{{__('Leilão')}}</th>
                            <th class="text-end">{{__('Valor')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($lances_vencendo as $k=>$v )
                                <tr>
                                    <td>{{App\Qlib\Qlib::dataExibe($v['post_date'])}}</td>
                                    <td class="text-center">
                                        <a class="btn btn-link" href="{{url('/')}}/leiloes-publicos/{{$v['leilao_id']}}">
                                            {{$v['leilao_id']}}
                                        </a>
                                    </td>
                                    <td class="text-end">{{App\Qlib\Qlib::valor_moeda($v['valor_lance'])}}</td>
                                </tr>
                            @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        <div class="card-footer d-print-none">

        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                {{__('Lances Superados')}}
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @if(isset($lances_superados) && is_array($lances_superados))
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="30%">{{__('Data')}}</th>
                                <th class="text-center">{{__('Leilão')}}</th>
                                <th class="text-end">{{__('Valor')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                                @foreach ($lances_superados as $k=>$v )
                                    <tr>
                                        <td>{{App\Qlib\Qlib::dataExibe($v['post_date'])}}</td>
                                        <td class="text-center">
                                            <a class="btn btn-link" href="{{url('/')}}/leiloes-publicos/{{$v['leilao_id']}}">
                                                {{$v['leilao_id']}}
                                            </a>
                                        </td>
                                        <td class="text-end">{{App\Qlib\Qlib::valor_moeda($v['valor_lance'])}}</td>
                                    </tr>
                                @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        <div class="card-footer d-print-none">

        </div>
    </div>
</div>
@else
    @if (isset($dados['list_lances']) && is_array($dados['list_lances']))
        <table class="table  table-condensed table-bordered " id="tbl-lances-realizados">
            <tbody>
                <thead>
                    <tr>
                        <th>{{__('Data')}} / {{__('Hora')}}</th>
                        <th>{{__('Usuário')}}</th>
                        <th>{{__('Valor')}}</th>
                    </tr>
                </thead>
                @foreach ($dados['list_lances'] as $kll=>$vll )
                    @php
                        $lance_automatico = false;
                        if(isset($vll->config) && $vll->config!=null){
                            $c = stripslashes($vll->config);
                            $c = str_replace('"{','{',$c);
                            $c = str_replace('}"','}',$c);
                            $arr_c = App\Qlib\Qlib::lib_json_array($c);
                            if(isset($arr_c['type']) && $arr_c['type']=='auto'){
                                $lance_automatico = '<span class="lance-aut text-primary" title="'.__('Lance Automático').'">(A)</span>';
                            }
                        }
                    @endphp
                    <tr class="">
                        <td>
                            @can('is_dev')
                                {{@$vll->id}} -
                            @endcan
                            {{$vll->data}}
                        </td>
                        <td class="text-center">
                            @can('is_admin2')
                                <a href="{{url('/admin/users/'.@$vll->autor.'/show')}}?redirect={{route('leiloes_adm.show',['id'=>@$vll->leilao_id])}}" class="underline">
                                    {{@$vll->name}}
                                </a>
                            @else
                            {{App\Qlib\Qlib::criptString(@$vll->name)}}
                            @endcan
                        </td>
                        <td class="td-lance">
                            {{App\Qlib\Qlib::valor_moeda($vll->valor_lance,'R$ ')}} {!!$lance_automatico!!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif
