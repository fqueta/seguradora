<div class="col-md-12 mt-2 mb-3">
    {{-- <div class="btn-toolbar mb-3" role="toolbar" aria-label=""> --}}
        <div class="btn-group row w-100 px-0" role="group" aria-label="">
            @php
                $activeRelatorio = false;
                $activeLista = false;
                if(Request::segment(2)=='sics'){
                    $activeRelatorio = 'active';
                    $activeLista = false;
                }
                if(Request::segment(2)=='sic'){
                    $activeRelatorio = '';
                    $activeLista = 'active';
                }
                if(Auth::check()){
                    $tamBtn = '4';
                }else{
                    $tamBtn = '3';
                }
            @endphp
            @if(Auth::check())
            @else

            <div class="col-md-{{$tamBtn}} pl-0">
                <a href="{{route('login')}}" class="btn btn-outline-primary w-100"><i cl
                    ass="fas fa-sign-in-alt"></i>  {!!__('Cadastre-se')!!} / {!!__('Login')!!}
                </a>
            </div>
            @endif
            <div class="col-md-{{$tamBtn}}">
                <a href="{{route('sic.create')}}" class="btn btn-outline-primary w-100">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i> {!!__('Nova Solicitação')!!}
                </a>
            </div>
            <div class="col-md-{{$tamBtn}}">
                <a href="{{route('sic.index')}}" class="btn btn-outline-primary w-100 {{$activeLista}}">
                    <i class="fa fa-search" aria-hidden="true"></i> {!!__('Minhas Solicitações')!!}
                </a>
            </div>
            <div class="col-md-{{$tamBtn}} pr-0">
               <a href="{{route('sic.internautas.relatorios')}}#titulo-relatorio" class="btn btn-outline-primary w-100 {{$activeRelatorio}}">
                    <i class="fas fa-chart-area" aria-hidden="true"></i> {!!__('Relatórios Estatísticos')!!}
                </a>

            </div>
        </div>
    {{-- </div> --}}
</div>
