@extends('adminlte::page')
@section('title')
{{$titulo}} - {{config('app.name')}} {{config('app.version')}}
@stop
@section('content_header')
    <h3>{{$titulo}}</h3>
@stop
@php

@endphp
@section('content')
<div class="row">
    <div class="col-md-12 mens">
        {{-- {{ App\Qlib\Qlib::formatMensagem( $_GET) }} --}}
    </div>
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{$title_card1}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($step==1)
                    <div class="row">
                        <div class="col-md-12">
                            <label>CPF do dono do contrato</label>
                            <input type="tel" mask-cpf value="{{@$_GET['cpf']}}" class="form-control" placeholder="Informe o CPF" name="cpf" id="cpf">
                        </div>
                    </div>
                @elseif ($step==2 && isset($config['user']['id']))
                    <div class="row">
                        <div class="col-md-6">
                            <label for="cliente">{{__('Cliente')}}: </label>
                            <span>
                                 {{@$config['user']['name']}}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label for="cpf">{{__('CPF')}}: </label>
                            <span> {{@$config['user']['cpf']}} </span>
                        </div>
                        <div class="col-md-6">
                            <label for="email">{{__('Email')}}: </label>
                            <span> {{@$config['user']['email']}} </span>
                        </div>
                        <div class="col-md-6">
                            <label for="celular">{{__('Celular')}}: </label>
                            <span> {{@$config['user']['config']['celular']}} </span>
                        </div>
                        <div class="col-12 border-bottom">
                            <h5>{{__('Endereço')}}</h5>
                        </div>
                        <div class="col-md-12">
                            <label for="endereco">{{__('Endereço')}}: </label>
                            <span> {{@$config['user']['config']['endereco']}} </span>,
                            <span> {{@$config['user']['config']['numero']}} </span>
                            <span> {{@$config['user']['config']['complemento']}} </span>
                        </div>
                        <div class="col-md-6">
                            <label for="bairro">{{__('Bairro')}}: </label>
                            <span> {{@$config['user']['config']['bairro']}} </span>,
                        </div>
                        <div class="col-md-6">
                            <label for="bairro">{{__('Bairro')}}: </label>
                            <span> {{@$config['user']['config']['bairro']}} </span>
                        </div>
                        <div class="col-md-6">
                            <label for="cidade">{{__('Cidade')}}: </label>
                            <span> {{@$config['user']['config']['cidade']}} </span> -
                            <span> {{@$config['user']['config']['uf']}} </span>
                        </div>
                        <div class="col-12 text-right">
                            <a href="{{route('users.edit',['id'=>@$config['user']['id']])}}" target="_blank"> <i class="fas fa-pen    "></i> {{__('Editar cliente')}} </a>
                        </div>
                        <div class="col-12 border-bottom">
                            <h5>{{__('Contrato')}}</h5>
                        </div>
                        <div class="col-12">
                            <form action="" method="post" id="frm-contrato">
                                <input type="hidden" name="user_id" id="user_id" value="{{@$config['user']['id']}}" />
                                <input type="hidden" name="config[cliente]" id="config_cliente" value="{{@$config['user']['id']}}" />
                                <div class="row">
                                    @if(isset($config['arr_contratos']) && is_array($config['arr_contratos']))
                                        <select name="contrato" id="contrato" class="form-control">
                                            <option value="" selected>{{__('Selecione um contrato')}}</option>
                                            <option value="cad" class="bg-secondary"> {{('Cadastrar um contrato')}} </option>
                                            <option disabled>-------------</option>
                                            @foreach ($config['arr_contratos'] as $k=>$v )
                                                <option value="{{$k}}" {{@$v['attr_option']}} >
                                                    {{$v['label']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                {{-- {{App\Qlib\Qlib::formulario([
                    'campos'=>$campos,
                    'config'=>$config,
                    'value'=>$value,
                ])}} --}}
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6">
                        <a href="{{$prev_page}}" step="{{$step}}" btn-prev class="btn btn-default"><i class="fa fa-chevron-left" aria-hidden="true"></i> Voltar </a>
                    </div>
                    <div class="col-6 text-right">
                        <a href="{{$next_page}}" step="{{$step}}" btn-next class="btn btn-primary">Avançar <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@stop

@section('css')
    @include('qlib.csslib')
@stop

@section('js')
    @include('qlib.jslib')
    <script type="text/javascript">
        function quick_cad_cliente(cfg){
            let base_url = cfg.base_url?cfg.base_url:'';
            if(!base_url){
                alert('Url base not specified')
                return;
            }
            if(cpf=cfg.cpf){
                getAjax({
                    url:'/admin/users?ajax=s&filter[cpf]='+cpf
                },function(res){
                    $('#preload').fadeOut("fast");
                    try {
                        if(user_id=res.dados.data[0].id){
                            cfg.user = res.dados.data[0];
                            // cfg.next = res.dados.data[0];
                            url = base_url+'?step=2&cfg='+encodeArray(cfg);
                            window.location = url;
                        }else{
                            // url = base_url+'?step=2&cfg='+encodeArray(cfg);
                            url = '/admin/users/create?cpf='+$('#cpf').val();
                            window.location = url;
                            alert('cliente não cadastrado');
                        }
                    } catch (e) {
                        if(typeof res.dados.data[0]=='undefined'){
                            if(window.confirm('Cliente não cadastrado deseja cadastrar agora?')){
                                var ura = urlAtual()+'?cpf='+$('#cpf').val();
                                url = '/admin/users/create?cpf='+$('#cpf').val()+'&bc=false&lbs=Salvar e continuar&step=1&quick_cad=leilao&rbase='+btoa(ura);
                                window.location = url;
                            }
                        }
                        console.log(e);
                    }
                });
            }
        }
        function url_contrato(){
            var dc = $('#frm-contrato').serialize(),user_id=$('#user_id').val(),url='/admin/produtos/create?config[cliente]='+user_id+'&bc=false&lbs=Salvar e prosseguir&rbase='+btoa(urlAtual());
            // url = encodeURIComponent(url);
            // console.log(url);
            return url;
        }
          $(function(){
            const urlParams = new URLSearchParams(urlAtual());
            const cf = urlParams.get('cfg');
            // $('#contrato').on('change',function(e){
            //     if($(this).val()=='cad'){
            //         var url=url_contrato();
            //         window.location = url;
            //     }
            //     console.log(dc);
            // });
            $('a.print-card').on('click',function(e){
                openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
            });
            $('[btn-next]').on('click',function(e){
                e.preventDefault();
                let link = $(this).attr('href');
                let s = $(this).attr('step');
                if(s==1){
                    quick_cad_cliente({
                        cpf:$('#cpf').val(),
                        base_url:"{{route('quick.add.leilao')}}",
                    });
                }
                if(s==2){
                    var cont = $('#contrato').val();
                    if(cont){
                        $('#contrato').addClass('valid').removeClass('error');
                    }else{
                        $('#contrato').addClass('error').removeClass('valid');
                        alert('Selecione um contrato');
                        return;
                    }
                    try {
                        if(cf){
                            cfg = decodeArray(cf);
                            console.log(cfg);
                            if(cont=='cad'){
                                var url=url_contrato();
                                window.location = url;
                                return;
                            }
                            var urlCadLeilao = '/admin/leiloes_adm/create?post_author='+cfg.user.id+'&bc=true&bs=false&contrato='+$('#contrato').val()+'&lbp=Salvar e prosseguir&rbase='+btoa(urlAtual());
                            window.location = urlCadLeilao;
                        }
                    } catch (error) {
                        console.log(error);

                    }
                    // quick_cad_leilao();
                }
            });
            $('#inp-password').val('');
            $('[mask-cpf]').inputmask('999.999.999-99');
            $('[mask-cnpj]').inputmask('99.999.999/9999-99');
            $('[mask-data]').inputmask('99/99/9999');
            $('[mask-cep]').inputmask('99.999-999');
          });
    </script>
    {{-- @include('qlib.js_submit') --}}
@stop
