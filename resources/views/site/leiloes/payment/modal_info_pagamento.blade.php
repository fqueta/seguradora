<!-- Button trigger modal -->
@php
    $as = isset($info_asaas)?$info_asaas:[];
    $front = App\Qlib\Qlib::is_frontend();
@endphp
@if($front)
    <div class="col-12 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            {{__('Informações')}}
        </button>
    </div>

    <!-- Modal -->
    <div class="modal fade " id="exampleModal" tabindex="-1" aria-labelledby="modal-info{{$id}}" aria-hidden="true">
        <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-info{{$id}}">{{__('Informações do Pagamento')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('site.leiloes.payment.info_pagamento',[
                    'as'=>$as
                ])
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Fechar')}}</button>
            </div>
        </div>
        </div>
    </div>
@else
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-info{{$id}}">
        {{__('Informações')}}
    </button>
    <!-- Modal -->
    <div class="modal fade text-left" id="modal-info{{$id}}" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Informações do Pagamento')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('site.leiloes.payment.info_pagamento',[
                        'as'=>$as
                    ])
                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-primary">Save</button> --}}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Fechar')}}</button>
                </div>
            </div>
        </div>
    </div>
@endif
