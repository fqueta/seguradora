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
    @if ($slug=='home')
        {{-- @include('site.layout.banner-home') --}}
    @endif
    @include('site.meio404')
@stop
