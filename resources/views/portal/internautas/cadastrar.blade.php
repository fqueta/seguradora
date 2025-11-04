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
        <div class = "page-header">
            <h1 class="text-center">
                {{$titulo}}
            </h1>
        </div>
        @php
            $_GET['redirect'] = route('internautas.index');
        @endphp
        {{App\Qlib\Qlib::formulario([
            'campos'=>$campos,
            'config'=>$config,
            'value'=>$value,
        ])}}

</div>
@endsection

@section('css')
    @include('portal.css')
@endsection

@section('js')
    @include('portal.js')
    @include('portal.js_submit')
@endsection
