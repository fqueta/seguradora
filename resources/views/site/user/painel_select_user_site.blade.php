<section>
    <div class="container">
        <div class="row py-5">
            <div class="col-6">
                <a href="{{url('/user/create/pf')}}" class="w-100 btn btn-outline-secondary"> {{__('Eu sou aluno')}} </a>
            </div>
            <div class="col-6">
                {{-- <a href="{{url('/user/create/pj')}}" class="w-100 btn btn-outline-secondary"> {{__('Eu sou escola')}} </a> --}}
                <button type="button" onclick="primeira_etapa_cadastro_site('empresa')" class="w-100 btn btn-outline-secondary"> {{__('Eu sou escola')}} </button>
            </div>
        </div>
    </div>
</section>
