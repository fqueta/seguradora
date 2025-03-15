<div class="modal fade {{$config['tam']}}" id="{{@$config['id']}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="{{@$config['id']}}Label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="{{@$config['id']}}-title">{{__(@$config['title'])}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @if (isset($config['include']) && !empty($config['include']))
                @include($config['include'])
            @else
                {!!@$config['conteudo']!!}
            @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Fechar')}}</button>
          {{-- <button type="button" class="btn btn-primary">Understood</button> --}}
        {!!@$config['bt_acao']!!}
        </div>
      </div>
    </div>
  </div>
