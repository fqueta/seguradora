@extends('layouts.principal')
@section('nav')
    @include('portal.nav')
@endsection

@section('content')
<section class="services">
    <div class="container">
        @include('portal.sic_front.painel')
        <div class="row">
            @include('admin.sic.exibe_relatorios')
        </div>
    </div>
</section>
@endsection

@section('css')
    @include('portal.css')
@endsection

@section('js')
    @include('portal.js')
@endsection
