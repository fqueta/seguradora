@php
    $slug = isset($dados['post_name']) ? $dados['post_name'] : false;
    $title = isset($dados['post_title']) ? $dados['post_namtitle'] : false;
    $mein = isset($dados['post_content']) ? $dados['post_content'] : false;
@endphp
@extends('site.layout.app')
@section('title')
    Erro: 404
@stop
@section('banner-topo')
    @if ($slug=='home')
        @include('site.layout.banner-home')
    @else
        @include('site.layout.banner-sec')
    @endif
@stop
@section('main')
    {{-- @if ($slug=='home')
    @endif
    @include('site.meio404') --}}
    <div class="container">
        <h2 class="headline text-warning">403</h2>
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Usuário sem permissão.</h3>
        <p>
            Este usuário encontra-se suspenso, ou impossibilitado de usar a nossa plataforma. Entre em contato para mais informações, <a href="https://aeroclubejf.com.br/contato">Contato</a>.
        </p>
        <form class="search-form">
            <div class="input-group">
                <!--<input type="text" name="search" class="form-control" placeholder="Search">
                <div class="input-group-append">
                    <button type="submit" name="submit" class="btn btn-warning"><i class="fas fa-search"></i>
                    </button>
                </div>-->
            </div>
        </form>
    </div>
@stop


