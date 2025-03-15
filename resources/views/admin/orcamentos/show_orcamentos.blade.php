@php
    $orcamento_html = isset($dados['orcamento_html']) ? $dados['orcamento_html'] : [];
@endphp
@if($orcamento_html)
    <div class="row">
        <div class="col-12">

            <div class="card card-outline-primary">
                <div class="card-header">
                    {{__('Detalhes')}}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!!$orcamento_html!!}
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted text-right">
                    {{-- @if(!isset($dados['leilao']['ID']) && isset($dados['token']))

                            <a href="{{url('/admin/leiloes_adm/create?post_author=').$dados['config']['cliente']}}&bc=true&bs=false&contrato={{$dados['token']}}&lbp=Salvar e prosseguir" class="btn btn-secondary">{{__('Criar leilão agora')}} <i class="fas fa-chevron-circle-right"></i></a>
                    @endif --}}
                </div>
            </div>
        </div>
    </div>
@endif
