@php
$post = isset($_REQUEST['post']) ? $_REQUEST['post'] : false;
$seg1 = request()->segment(1); //link da página em questão
$seg2 = request()->segment(2); //link da página em questão
$urlB = App\Qlib\Qlib::get_slug_post_by_id(37); //link da pagina para cosulta de leiloes no site.
// dd($dados1);
@endphp
<style>
    .div-salvar {
        position: initial;
        margin-top:10px;
    }
</style>

 <!-- Content -->
 @if($seg1==$urlB && !$seg2)
    <style>
        .main-banner{
            background-image: url('{{App\Qlib\Qlib::get_thumbnail_link(@$post['ID'])}}');
        }
    </style>
    <section class="main-banner">
        <div class="main-banner-content container">
            <div class="row pt-5">
                <div class="col-12 col-lg-4 py-5 text-center text-lg-start">
                    <h2>Bem-vindo ao Leilão de Horas de Voo!</h2>
                    <p>
                        {!!$post['post_excerpt']!!}
                    </p>
                </div>
            </div>
        </div>
        <div class="main-banner-overlay"></div>
    </section>
    <!-- Info cards -->
    <section class="info-cards mb-5">
        <div class="container">
            <div class="info-cards-content theme-bg-primary rounded">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="info-card">
                            <i class="fa-solid fa-lock"></i>
                            <h4>Segurança</h4>
                            <p>
                                {{App\Qlib\Qlib::qoption('texto_card_seguranca')}}
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 middle-card">
                        <div class="info-card">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <h4>Economia</h4>
                            <p>
                                {{App\Qlib\Qlib::qoption('texto_card_economia')}}
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="info-card">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <h4>Transparência</h4>
                            <p>
                                {{App\Qlib\Qlib::qoption('texto_card_transparencia')}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="bg-video">
        <div class="container">
            <div class="row justify-content-center py-5">
                <div class="col-12 col-lg-6">
                    <h3 class="text-center mb-4"><b>Como funciona:</b></h3>
                    <a class="venobox youtube-video" data-autoplay="true" data-vbtype="video"
                        href="https://www.youtube.com/watch?v=BZAN-i_o8nA&t=5s">
                        <i class="fa fa-play"></i>
                        <div class="youtube-video-overlay rounded"></div>
                        <img class="rounded" src="{{url('/images/leilao-aeroclube-de-juiz-de-fora.jpg')}}" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>
    <section class="bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="search-block rounded mb-5 p-4">
                        <form action="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(37)}}">
                            <div class="input-group">
                                <input type="text" name="filter[post_title]" value="{{@$_GET['filter']['post_title']}}" class="form-control" placeholder="{{__('Buscar leilões')}}"
                                aria-label="{{__('Buscar leilões')}}" aria-describedby="basic-addon2">
                                <span class="input-group-btn" id="basic-addon2">
                                    <button id="btn-pesq-jogo" class="btn btn-search" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                                @can('is_customer_logado')
                                    <span class="input-group-text" id="basic-addon2">
                                            <a href="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(2)}}" class="btn btn-success fa fa-plus btn-labeled mar-top">{{__('Criar um Leilão')}}</a>
                                    </span>
                                @endcan
                            </div>
                        </form>
                    </div>
                    <h2 class="title-page text-center mb-5">Leilões disponíveis</h2>
                </div>
            </div>
            <div class="items-list mb-4">
                @php
                $li = false;
                $class_lc = new App\Http\Controllers\LeilaoController;
                @endphp
                @if (is_object($dados) || is_array($dados))
                    @foreach ($dados as $k=>$v )
                        @php
                            // $v['src'] = App\Qlib\Qlib::get_thumbnail_link($v['ID']);
                            // $v['link'] = App\Qlib\Qlib::get_the_permalink($v['ID'],$v);
                            // $v['link_edit_admin'] = (new App\Http\Controllers\LeilaoController)->get_link_edit_admin($v['ID'],$v);
                            $info_termino = $class_lc->info_termino($v['ID']);
                            // $li .= view('site.leiloes.grid',[
                            //     'v'=>$v,
                            //     'info_termino'=>$info_termino['html'],
                            //     'arr_termino'=>$info_termino
                            // ]);
                        @endphp
                        @include('site.leiloes.grid',[
                            'v'=>$v,
                            'info_termino'=>$info_termino['html'],
                            'arr_termino'=>$info_termino
                        ])
                    @endforeach
                    {{-- @php
                        echo $li;
                    @endphp --}}
                @endif
            </div>


            {{-- <div class="items-nav d-flex justify-content-center">
                <nav aria-label="Navegação de leilões">
                    <ul class="pagination">
                        <li class="page-item"><a class="page-link" href="#"><i class="fa fa-chevron-left"></i></a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#"><i class="fa fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div> --}}
        </div>
    </section>
@else
        @if($seg1==$urlB && $seg2)
            @include('site.leiloes.detalhes',['dados'=>@$dados,'config'=>$config])
        @else
            @include('site.leiloes.list_leiloes_ganhos')
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        @if (!empty($arr_titulo))
                            Lista de:

                            @foreach ($arr_titulo as $k=>$pTitulo)
                                <label for=""> Todo com {{ $k }}</label> = {{ $pTitulo }}, e
                            @endforeach
                        @else
                            {{ $titulo_tabela }}
                        @endif
                        <div class="float-end"><a href="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(2)}}" class="btn btn-primary"> <i class="fas fa-plus"></i> {{__('Novo cadastro')}}</a></div>
                        </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{
                            App\Qlib\Qlib::listaTabela([
                            'campos_tabela'=>$campos_tabela,
                            'config'=>$config,
                            'dados'=>$dados,
                            'routa'=>$routa,
                            //   'fileLista'=>'listaTabelaSite',
                        ])}}
                    </div>
                </div>
                <div class="card-footer d-print-none">
                    <div class="table-responsive">
                        @if ($config['limit']!='todos')
                        {{ $dados->appends($_GET)->links() }}
                        @endif
                    </div>
                </div>
            </div>
        @endif
@endif
<script>
    $(function(){
        $('[exportar-filter]').on('click',function(e){
            e.preventDefault();
            var urlAtual = window.location.href;
            var d = urlAtual.split('?');
            url = '';
            if(d[1]){
                url = $(this).attr('href');
                url = url+'?'+d[1];
            }
            if(url)
                abrirjanelaPadrao(url);
                //window.open(url, "_blank", "toolbar=1, scrollbars=1, resizable=1, width=" + 1015 + ", height=" + 800);
            //confirmDelete($(this));
        });
        $('[data-del="true"]').on('click',function(e){
            e.preventDefault();
            confirmDelete($(this));
        });
        $('[name="filter[cpf]"],[name="filter[cpf_conjuge]"]').inputmask('999.999.999-99');
        $(' [order="true"] ').on('click',function(){
            var val = $(this).val();
            var url = lib_trataAddUrl('order',val);
            window.location = url;
        });
    });
</script>
