@php
    $ultimos_dias = 'd-none';
    if($arr_termino['color'] == 'text-danger'){
        $ultimos_dias = '';
    }
@endphp
<div class="product-item card border-0 shadow-sm">
    <div class="type-item bg-success text-center text-light"><small>{{__('Leilão')}}</small></div>
    <div class="product-item-img">
        <span class="badge bg-danger {{$ultimos_dias}}">{{__('Últimos dias')}}</span>
        <div class="product-item-img-overlay"></div>
        <img src="{{$v['src']}}" class="card-img-top" alt="{{$v['post_title']}}">
    </div>
    <div class="card-body text-center">
        <div class="product-item-title rounded shadow p-2">
            <h5>{{$v['post_title']}}</h5>
        </div>
        {{-- <p class="text-muted"><small>RJ - Campo Grande</small></p> --}}
        <p class="mb-0">{{__('Termina em')}}
            {{-- {{$info_termino}} --}}
            <span class="{{@$arr_termino['color']}}">
                <b>
                    {{@$arr_termino['time']}}
                </b>
            </span>
        </p>
        <p class="text-muted"><small>{{@$arr_termino['data']}}</small></p>
        <p><small>{{__('Próximo lance')}}:</small><br> <b>{{App\Qlib\Qlib::valor_moeda($v['proximo_lance'],'R$')}}</b></p>
        <a href="{{$v['link']}}" class="btn btn-success btn-sm w-100">{{__('Ver leilão')}}</a>
        @can('is_admin2')
            <a href="{{$v['link_edit_admin']}}" class="btn btn-outline-secondary w-100 mt-2">{{__('Edit')}}</a>
        @endcan
    </div>
</div>
