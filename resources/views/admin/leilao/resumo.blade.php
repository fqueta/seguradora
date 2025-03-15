@if (isset($d['total_views']) || isset($d['total_seguidores']))

    <div class="row mb-3">
        <div class="col-12 bg-secondary">
            <h4 class="bg-secondary">Resumo</h4>
        </div>
        <div class="col-6">
            <label for="">{{__('Visualizações')}}</label>:
            {{@$d['total_views']}}
        </div>
        <div class="col-6">
            <label for="">{{__('Seguidores')}}</label>:
            {{@$d['total_seguidores']}}
        </div>
        @if(isset($d['situacao_html']))
        <div class="col-6">
            {!!$d['situacao_html']!!}
        </div>
        @endif
    </div>

@endif
