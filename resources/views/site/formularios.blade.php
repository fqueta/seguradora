@php
$post = isset($_REQUEST['post']) ? $_REQUEST['post'] : false;
$mes = isset($config['mes']) ? $config['mes'] : false;
@endphp
<div class="flash-message">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-' . $msg))
            <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}</p>
        @endif
    @endforeach
    @if ($mes)
      <p class="alert alert-info">{{ $mes }}</p>
    @endif
</div>
@if (isset($config['title']))
    @section('title')
        {{$config['title']}}
    @endsection
@endif
<style>
    .div-salvar {
        position: initial;
        margin-top:10px;
    }
</style>
<div class="row">
    <div class="col-md-12 mens">
    </div>
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{@$config['titulo']}}</h3>
            </div>
            <div class="card-body">
                {{App\Qlib\Qlib::formulario([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}}
            </div>
        </div>
    </div>
    @if($config['ac']=='alt')

    @endif
    @include($config['file_submit'])
</div>
