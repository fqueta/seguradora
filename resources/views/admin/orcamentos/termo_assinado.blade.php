@php
    $redirect_base = request()->get('redirect_base');
    $redirect = base64_decode($redirect_base);
@endphp
@extends('adminlte::page')

@section('title', 'Ter')

@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@include('admin.partes.header')
@section('content')
    <div class="row">
        <div class="col-md-12">
            {!!$termo!!}
        </div>
        <div class="col-md-12 div-salvar bg-light d-print-none">
            <a type="button" btn-volter="true" href="{{$redirect}}" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i> Voltar</a>
            <button type="button" btn="print" onclick="window.print();" class="btn btn-outline-primary"><i class="fas fa-print"></i></button>
            {{-- <a href="http://localhost:8000/admin/orcamentos/58/edit?redirect=http://localhost:8000/admin/orcamentos/58?redirect=http%3A%2F%2Flocalhost%3A8000%2Fadmin%2Forcamentos%3FidCad%3D58" btn="editar" class="btn btn-outline-primary"> <i class="fa fa-pen" aria-hidden="true"></i> Editar</a> --}}
        </div>
    </div>
@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    {{-- <script type="text/javascript">
        $(function(){
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
        });

    </script> --}}
@stop

