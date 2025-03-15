{{-- @php
    dd($dados);
@endphp --}}
@if(isset($dados['config']['cliente']) && !empty($dados['config']['cliente']))
    <div class="row">
        <div class="col-12">

            <div class="card card-outline-primary">
                <div class="card-header">
                    {{__('Leilão deste contrato')}}
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- <h4 class="card-title">Title</h4> --}}
                        @if(@$dados['leilao']['ID'])
                            <div class="col-md-12 text-left mb-4">
                                <p class="card-text">
                                    {{__('Contrato integrado no leilão')}}: <a style="text-decoration:underline;" href="{{url('/admin')}}/leiloes_adm/{{@$dados['leilao']['ID']}}/edit?redirect={{url('/admin')}}/leiloes_adm?idCad={{@$dados['leilao']['ID']}}">{{@$dados['leilao']['post_title']}}</a>
                                </p>
                            </div>
                        @else
                            <p class="card-text">
                                {{__('Ainda não existe leilão criado para este contrato. Se desejar cadastrar um leilão para este cliente use o boão abaixo.')}}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="card-footer text-muted text-right">
                    @if(!isset($dados['leilao']['ID']) && isset($dados['token']))

                            <a href="{{url('/admin/leiloes_adm/create?post_author=').$dados['config']['cliente']}}&bc=true&bs=false&contrato={{$dados['token']}}&lbp=Salvar e prosseguir" class="btn btn-secondary">{{__('Criar leilão agora')}} <i class="fas fa-chevron-circle-right"></i></a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
