@if (isset($dados['ID']) && isset($dados['arr_lances']) && is_array($dados['arr_lances']))
    @php
        $disbledL = 'disabled';
        // $mensLog = '<div class="alert alert-danger">'.__('Para dar lances é necessário estar logado no site.').' <!--<a href="'.route('login').'" class="text-decoration-underline"> Logar</a>--></div>';
        $mensLog = '<p class="text-danger mt-2"><small><i class="fa-solid fa-triangle-exclamation"></i>'.__('Você precisa estar logado para dar lances!').'</small></p>';
        if(Gate::allows('is_admin2')||Gate::allows('is_customer_logado')){
            $disbledL = '';
            $mensLog = false;
        }
    @endphp
    <style>
        .custom-select {
        background: #ffffff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3e%3cpath fill='black' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") no-repeat right .75rem center/8px 10px !important;
        }
    </style>
    <form id="frm-lance" method="post" action="{{route('lances.store')}}">
        @csrf
        <input type="hidden" name="leilao_id" value="{{$dados['ID']}}" />
        <input type="hidden" name="origem" value="front" />
        <div class="page-description text-center text-md-start px-3 px-md-0">
            @if(isset($dados['total_horas_lance']) && $dados['total_horas_lance']>0 && @$dados['proximo_lance']>0)
                <p>{{__('Com o valor de lance ')}}<b>{{App\Qlib\Qlib::valor_moeda($dados['proximo_lance'],'R$')}} </b>{{__('O valor da hora é de ')}} <b>{{App\Qlib\Qlib::valor_moeda(@$dados['total_horas_lance'],'R$')}} </b></p>
            @endif
            <div class="input-group" style="max-width: 350px;">
                <select name="valor_lance" class="form-control custom-select" id="valor_lance">
                    @foreach ($dados['arr_lances'] as $kl=>$vl)
                    <option value="{{$vl['valor']}}">{{Number_format($vl['valor'],2,',','.')}}</option>
                    @endforeach
                </select>
                <button id="btn-frm-lance" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#modal-dar-lance" {{$disbledL}} type="button"><i class="fa-solid fa-gavel"></i> {{__('Dar meu lance')}}
                </button>
            </div>
            @php
                echo $mensLog;
            @endphp
            <p class="text-muted">
                {!!(new App\Http\Controllers\LanceController)->info_reserva($dados['ID'])!!}
            </p>
            <p class="text-muted"><small><b>Obs: *</b> {{__('Valores acima do valor mínimo de lance entrarão
                    como valor de
                    reserva para lances automáticos.')}}</small></p>
            <hr>
            @if(isset($dados['desconto_s_atual']['valor']) && $dados['desconto_s_atual']['valor']>0)
                <p>{{__('Ao realizar o lance acima você tem uma economia de ')}}<br> <b class="text-success">{{@$dados['desconto_s_atual']['html']}} </b> (<b>{{@$dados['desconto_s_atual']['porcento']}}% OFF </b>) {{__('Sobre o preço atual do pacote.')}}</p>
                @if(isset($dados['valor_atual']))
                    <p>{{__('Preço atual do pacote')}} {{$dados['valor_atual']}}</p>
                @endif
            @endif
            {{-- {{dd($dados)}} --}}
            @if(isset($dados['config']['valor_venda']) && @$dados['exibe_btn_comprar'] && !$finalizado)
                <div class="cart-area">
                    <p>{{__('Ou')}}  <button type="button" onclick="window.location='{{$dados['link_btn_comprar']}}'" class="btn btn-sm theme-btn-primary px-3"><i class="fa-solid fa-shopping-cart"></i> {{__('Compre já')}}</button> {{__('por')}}
                        <b>{{$dados['config']['valor_venda']}}</b>
                    </p>
                </div>
            @endif
        </div>
        <div class="row mb-3">
            <div class="col-12 mens mb-3"></div>
            <div class="col-6">

            </div>
            <div class="col-6">

            </div>
        </div>
    </form>
        @include('site.partes_bs.modal',['config'=>[
            'id'=>'modal-dar-lance',
            'title'=>'Atenção',
            'tam'=>'',
            'bt_acao'=>'<button type="button" seguir-lance class="btn btn-primary">'.__('Prosseguir').'</button>',
            'include'=>false,
        ]])

    @php
        $redirect = App\Qlib\Qlib::UrlAtual();
    @endphp
    <script>
        $(function(){
            lib_gerLances('{{ $redirect }}');
        })
    </script>
@endif
