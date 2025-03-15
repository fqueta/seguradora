@if (isset($seguindo) && is_array($seguindo) && isset($dados_pg))
<div class="row">
    <div class="col-md-12">
        {{-- <h3>{{$dados_pg['post_title']}}</h3> --}}
    </div>
    <div class="col-md-12 table-resposive">
        {{-- {{dd($seguindo)}} --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Informações</th>
                    <th>Termino</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($seguindo as $v)
                    <tr class="tr-disabled" data-id_anuncio="{{$v['ID']}}">
                        <td class="td-contrato"><a href="{{$v['link_leilao']}}"><img class=" w-100 img-capa-xs" src="{{$v['link_thumbnail']}}"></a></td>
                        <td class="td-info">
                            <div class="anchor" id="id_leilao_item_{{$v['ID']}}"></div>
                            <a href="{{$v['link_leilao']}}">{{$v['post_title']}}</a>
                        </td>
                        <td class="td-leilao">
                            <span>{!!@$v['termino']['html']!!}</span><br>
                            {{-- <span class="sem-lance block">SEM LANCE</span> --}}
                        </td>
                        <td class="td-acao">
                            <a class="btn btn-primary w-100" href="{{$v['link_leilao']}}"><i class="fas fa-eye    "></i> Ver Leilão</a>

                            @if(isset($v['link_seguir']) && $v['link_seguir'])
                                <div class="col-12 text-end">
                                    <a href="{!!$v['link_seguir']!!}" class="btn btn-outline-{!!@$v['link_seguir_color']!!} w-100 mt-2" title="{!!@$v['link_seguir_title']!!}">
                                        @if(isset($v['total_seguidores']))
                                        <span class="" title="{{__('Total de pessoas seguindo esse leilão')}}">{{$v['total_seguidores']}}</span>
                                        @endif
                                        {{@$v['link_seguir_label']}}
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
