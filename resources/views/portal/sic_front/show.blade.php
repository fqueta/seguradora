@extends('layouts.principal')

@section('nav')
    @include('portal.nav')
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 mens">
        </div>
    </div>
    @can('is_user_front_v')
    <div class="card card-primary card-outline">
        <div class = "card-header">
            <h1 class="text-center">
                {{$titulo}}
            </h1>
        </div>
        <div class="card-body">
            @php
            $_GET['redirect'] = route('internautas.index');
            //$config['event'] = 'enctype="multipart/form-data"';
            //dd($config);
            @endphp

            {{App\Qlib\Qlib::show([
                'campos'=>$campos,
                'config'=>$config,
                'value'=>$value,
            ])}}
        </div>
    </div>
    @elsecannot('is_user_front_v')
        <h6 class="text-center">{{__('Somente internautas com cadastro verificados podem usar este serviço')}}</h6>
    @endcan

</div>
@endsection

@section('css')
    @include('portal.css')
    <style>
        label{
            font-weight: bold;
        }
    </style>
@endsection

@section('js')
    @include('portal.js')
    @include('portal.sic_front.js_submit')
@endsection
