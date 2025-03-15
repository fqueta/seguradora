@php
    global $post;
    $contrato = isset($dados['nome_contrato']) ? $dados['nome_contrato'] : '';
    $finalizado = isset($dados['finalizado']) ? $dados['finalizado'] : false;
    $thumb_page = App\Qlib\Qlib::get_thumbnail_link(@$post['ID']);
@endphp
@section('title')
{{$config['title']}}
@endsection
<style>
    .banner-page{
        background-image: url("{{$thumb_page}}");
    }
</style>
{{-- {{dd($dados)}} --}}
<section class="banner-page">
    <div class="container">
        <div class="row py-5">
            <div class="col-12 col-md-5 order-1 order-md-0">
                <div class="banner-page-sidebar">
                    <div class="banner-page-item-cover p-1 bg-light rounded text-center">
                        <img src="{{$dados['link_thumbnail']}}" alt="{{$dados['post_title']}}" class="img-fluid rounded mb-3">
                        <h3><i class="fa-solid fa-gavel"></i> {{__('Lance Atual')}}</h3>
                        <p class="display-6 text-muted mb-0">{!!$dados['lance_atual']!!}</p>
                        <button type="button" class="btn btn-link p-0" id="btn-ver_lances">{{__('Ver Lances')}} ({{$dados['total_lances']}})</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7 order-0 order-md-1">
                <div class="banner-page-content text-center text-md-start mb-5 mb-md-0">
                    <h2>{{$config['titulo']}}</h2>
                    <ul>
                        @if($contrato)
                        <li><i class="fa-solid fa-file-lines"></i> <b>{{__('Ficha')}}:</b> {{$contrato}}</li>
                        @endif
                        @if($dados['situacao']=='f')
                        <li>
                            <i class="fa-solid fa-clock"></i> {{__('Terminou em')}} <span class="text-danger"><b>{{$dados['info_termino']['time']}}</b></span>
                        </li>
                        @else
                        <li>
                            <i class="fa-solid fa-clock"></i> {{__('Termina em')}} <span class="{{@$dados['info_termino']['quase_termino']['color']}}"><b>{{$dados['info_termino']['time']}}</b></span>
                        </li>
                        @endif
                        <li>
                            <i class="fa-solid fa-calendar"></i> <b>{{@$dados['info_termino']['data0']}}</b> {{__('às')}} <b>{{@$dados['info_termino']['hora']}}</b>
                        </li>
                    </ul>
                    <div class="followers">
                        <a href="{!!$dados['link_seguir']!!}" title="{!!@$dados['link_seguir_title']!!}" class="badge bg-info text-{!!@$dados['link_seguir_color']!!} me-2">{{@$dados['link_seguir_label']}}</a><small><i class="fa-solid fa-heart"></i> <span title="{{__('Total de pessoas seguindo esse leilão')}}">{{$dados['total_seguidores']}} {{__('seguidores')}} </span> | <i
                                class="fa-solid fa-eye"></i> {{@$dados['total_views']}} {{__('visualizações')}}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="banner-page-overlay"></div>
</section>
<section class="pre-page bg-light">
    <div class="container">
        <div class="row py-5">
            <div class="col-12 col-md-5"></div>
            <div class="col-12 col-md-7">
                @if (isset($dados['info_termino']['exec']) && isset($dados['info_termino']['termino']))
                    @if ($dados['info_termino']['termino'])
                        <div class="col-12">
                            <label class="fw-bold" for="termino">{{__('Lance Vencedor')}}: </label><b> {!!@$dados['lance_vencedor']!!} </b>
                        </div>
                    @else
                        <div class="col-12">
                            @include('site.leiloes.dar_lances')
                        </div>
                    @endif
                @endif
                <div class="card-store card border-0">
                    <div class="card-body text-center text-md-start">
                        <h6><b>Métodos de pagamento</b></h6>
                        <ul class="d-flex justify-content-center justify-content-md-start">
                            <li class="me-4"><i class="fa-brands fa-pix"></i> Pix</li>
                            <li class="me-4"><i class="fa-solid fa-credit-card"></i> Cartão</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('site.partes_bs.modal',['config'=>[
        'id'=>'modalLances',
        'title'=>'Lances',
        'tam'=>'modal-lg',
        'bt_acao'=>false,
        'include'=>'site.leiloes.lances.list_lances',
    ]])
    <script>
        $(function(){
            $('#btn-ver_lances').on('click', function(){
                $('#modalLances').modal('show');
            });

        });
    </script>
</section>
<section class="page-content">
    <div class="container">
        <div class="row py-5">
            <div class="col-12">
                <h3 class="text-center text-md-start">Descrição do leilão</h3>
                @if (isset($dados['post_content']))
                    {!!$dados['post_content']!!}
                @endif
            </div>
        </div>
        @can('is_admin2')
            <div class="row py-5">
                        <div class="col-6">
                            @isset($_GET['redirect'])
                            <a href="{{$_GET['redirect']}}" class="btn btn-outline-secondary"> <i class="fa fa-chevron-left" aria-hidden="true"></i> {{__('Voltar')}}</a>
                            @endisset
                        </div>
                        <div class="col-6 text-end">
                            @isset($dados['ID'])
                                <a href="{{route('leiloes_adm.edit',['id'=>$dados['ID']]).'?redirect='.App\Qlib\Qlib::UrlAtual()}}" class="btn btn-outline-primary">
                                    {{__('Editar')}} <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            @endisset
                        </div>
           </div>
        @endcan
    </div>
</section>
@if(isset($dados['desconto_s_atual']['valor']) && $dados['desconto_s_atual']['valor']>0)
    <section class="bottom-msg">
        <div class="container">
            <div class="alert alert-primary alert-dismissible fade show text-center" role="alert">
                <h4 class="display-6">Economia</h4>
                    <p>{{__('Ao realizar o lance acima você tem uma economia de ')}}<br> <b class="text-success">{{@$dados['desconto_s_atual']['html']}} </b> (<b>{{@$dados['desconto_s_atual']['porcento']}}% OFF </b>) {{__('Sobre o preço atual do pacote.')}}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </section>
@endif
{{-- <div class="row">
    @if(isset($config['exec']) && $config['exec'])
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <img src="{{$dados['link_thumbnail']}}" alt="{{$dados['post_title']}}" class="w-100"/>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-12">
                <h2>
                    {{$config['titulo']}}
                </h2>
            </div>
            @if ($contrato)
            <div class="col-12">
                <label class="fw-bold" for="contrato">{{__('Contrato')}}: </label> {{$contrato}}
            </div>
            @endif
            <div class="col-12">
                <label class="fw-bold" for="termino">{{__('Término')}}: </label> {{$dados['termino']}}
            </div>
            <div class="col-12">
                <label class="fw-bold" for="termino">{{__('Lance Atual')}}: </label> {!!$dados['lance_atual']!!}
            </div>
            <div class="col-12">
                <a href="javascript:void(0);" id="btn-ver_lances">{{__('Ver Lances')}} ({{$dados['total_lances']}}) </a>
            </div>

            @if (isset($dados['info_termino']['exec']) && isset($dados['info_termino']['termino']))
                @if ($dados['info_termino']['termino'])
                    <div class="col-12">
                        <label class="fw-bold" for="termino">{{__('Lance Vencedor')}}: </label><b> {!!@$dados['lance_vencedor']!!} </b>
                    </div>
                @else
                    <div class="col-12 mb-3">
                        {!!App\Http\Controllers\LanceController::info_reserva($dados['ID'])!!}
                    </div>
                    <div class="col-12">
                        @include('site.leiloes.dar_lances')
                    </div>
                @endif
            @endif

            <div class="col-12 mb-3" style="text-align: justify">
                <small>* {{__('Valores acima do valor mínimo de lance entrarão como valor de reserva para lances automáticos.')}} </small>
            </div>

            @include('site.partes_bs.modal',['config'=>[
                'id'=>'modalLances',
                'title'=>'Lances',
                'tam'=>'modal-lg',
                'bt_acao'=>false,
                'include'=>'site.leiloes.lances.list_lances',
            ]])
            <script>
                $(function(){
                    $('#btn-ver_lances').on('click', function(){
                        $('#modalLances').modal('show');
                    });

                });
            </script>

            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">
                            {{__('Acompanhamento')}}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <i title="{{__('Visualizações')}}" style="position: relative;top: 0px" class="fa fa-eye"></i>
                                @if (isset($dados['total_views']))
                                    <span class="tot-count tot-count-view">{{$dados['total_views']}}</span>
                                @endif
                            </div>
                            @if(isset($dados['link_seguir']) && $dados['link_seguir'])
                                <div class="col-6 text-end">
                                    @if(isset($dados['total_seguidores']))
                                        <span class="total_seguidores" title="{{__('Total de pessoas seguindo esse leilão')}}">{{$dados['total_seguidores']}}</span>
                                    @endif
                                    <a href="{!!$dados['link_seguir']!!}" class="btn btn-outline-{!!@$dados['link_seguir_color']!!}" title="{!!@$dados['link_seguir_title']!!}">{{@$dados['link_seguir_label']}}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">
                            {{__('Formas de Pagamento')}}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Pix <svg width="23px" height="22" viewBox="0 0 22 23" preserveAspectRatio="xMidYMin" provider="" class="icon-pix pix-gray" data-v-d2a71a6c=""><path fill-rule="evenodd" clip-rule="evenodd" d="M5.19223 5.24323C6.06969 5.24323 6.89487 5.58498 7.51525 6.20516L10.8818 9.57225C11.1243 9.8147 11.5202 9.81575 11.7633 9.57193L15.1175 6.21736C15.738 5.59718 16.5632 5.25554 17.4407 5.25554H17.8447L13.5842 0.995107C12.2574 -0.331702 10.1063 -0.331702 8.77948 0.995107L4.53135 5.24323H5.19223ZM17.4406 17.108C16.5632 17.108 15.738 16.7664 15.1176 16.1462L11.7632 12.792C11.5278 12.5558 11.1173 12.5565 10.8819 12.792L7.51531 16.1585C6.89482 16.7786 6.06964 17.1202 5.19219 17.1202H4.5312L8.77943 21.3686C10.1062 22.6953 12.2574 22.6953 13.5842 21.3686L17.8447 17.108H17.4406ZM18.794 6.20484L21.3686 8.77947C22.6954 10.1062 22.6954 12.2573 21.3686 13.5842L18.7941 16.1587C18.7373 16.1359 18.6761 16.1218 18.6112 16.1218H17.4407C16.8354 16.1218 16.243 15.8764 15.8154 15.4484L12.4611 12.0945C11.8532 11.4859 10.7925 11.4862 10.184 12.0942L6.81744 15.4607C6.38976 15.8886 5.79746 16.134 5.19222 16.134H3.75286C3.69154 16.134 3.634 16.1486 3.57983 16.169L0.995108 13.5842C-0.331703 12.2573 -0.331703 10.1062 0.995108 8.77947L3.57994 6.19464C3.63411 6.21504 3.69154 6.22956 3.75286 6.22956H5.19222C5.79746 6.22956 6.38976 6.47496 6.81744 6.90285L10.1843 10.2697C10.4982 10.5833 10.9103 10.7404 11.3227 10.7404C11.7349 10.7404 12.1473 10.5833 12.4611 10.2694L15.8154 6.91505C16.243 6.48716 16.8354 6.24176 17.4407 6.24176H18.6112C18.676 6.24176 18.7373 6.22756 18.794 6.20484Z"></path></svg>
                            Cartão <i class="fas fa-credit-card"></i>
                        </p>
                    </div>
                </div>
            </div>
            @if(isset($dados['config']['valor_venda']) && @$dados['exibe_btn_comprar'] && !$finalizado)
            <!-- Comprar agora -->
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">
                            {{config('app.name')}} STORE
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="card-text">
                                    {{$dados['config']['valor_venda']}}
                                </p>
                            </div>
                            <div class="col-6 text-end">

                                <a href="{{$dados['link_btn_comprar']}}" class="btn btn-success"><i class="fas fa-cart-plus"></i>  {{__('Comprar')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>
                    {{__('Descrição do Leilão')}}
                </h5>
            </div>
            <div class="card-body">
                @if (isset($dados['post_content']))

                {!!$dados['post_content']!!}
                @endif
            </div>
            @can('is_admin2')
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            @isset($_GET['redirect'])
                            <a href="{{$_GET['redirect']}}" class="btn btn-outline-secondary"> <i class="fa fa-chevron-left" aria-hidden="true"></i> {{__('Voltar')}}</a>
                            @endisset
                        </div>
                        <div class="col-6 text-end">
                            @isset($dados['ID'])
                                <a href="{{route('leiloes_adm.edit',['id'=>$dados['ID']]).'?redirect='.App\Qlib\Qlib::UrlAtual()}}" class="btn btn-outline-primary">
                                    {{__('Editar')}} <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            @endisset
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
    @else
    <div class="col-md-12 mb-3">
        @if(isset($config['mens']))
        {!!$config['mens']!!}
        @endif
    </div>

    @endif

</div> --}}
