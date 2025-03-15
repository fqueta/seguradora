@php
    $local = request()->route()->getName();
@endphp
@if(isset($dados['motivo']))
<div class="card card-danger card-outline">
    <div class="card-header">
        <h4 class="card-title">
            {{__('Usuário no Blacklist')}}
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <label>{{__('Motivo')}}: </label>
                <span>{!!$dados['motivo']['description']!!}</span>
            </div>
            {{-- <div class="col-md-6">
                <label>{{__('Motivo')}}: </label>
                <span>{{$dados['motivo']['description']}}</span>
            </div> --}}
        </div>
    </div>
    @if(isset($dados['motivo']['link_front']))
    <div class="card-footer text-muted">
        <a href="{{@$dados['motivo']['link_front']}}" target="_blank" class="btn btn-default"><i class="fas fa-eye"></i> {{__('Ver leilão no site')}}</a>
        <a href="{{@$dados['motivo']['link_admin']}}" target="_blank" class="btn btn-default"><i class="fas fa-pen"></i> {{__('Editar o leilão')}}</a>
    </div>
    @endif
    @can('is_dev')
        @if ($local == 'users.edit')
            <div class="form-group col-md-12">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success  ">
                    <input type="checkbox" class="custom-control-input" value="n" name="meta[backlist]" id="blacklist_ativo">
                    <label class="custom-control-label" for="blacklist_ativo">Liberar do blacklist</label>
                </div>
            </div>
        @endif
    @endcan
</div>
@endif
