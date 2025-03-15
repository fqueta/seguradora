<!-- Shortcode: START -->

<style>
    .d-none {
        display: none;
    }

    .form-acjf .form-acjf-search {
        display: flex;
    }
    .form-acjf .form-acjf-search .form-control {
        width: 100%;
        padding: 5px 8px;
        font-size: 18px;
        border-radius: 8px 0 0 8px;
        border: solid #1d81bb 1px;
    }
    input[type="checkbox"]{
        width: auto !important;
        display: initial !important;
    }
    .form-acjf .form-acjf-search button {
        border-radius: 0 8px 8px 0;
        background-color: rgb(35, 35, 121);
        border: none;
        color: #fff;
        font-size: 16px;
        padding: 8px 20px;
    }

    .table tbody tr {
        margin: 0;
        border: none;
    }

    .table tbody tr th,
    .table tbody tr td {
        text-align: start;
        padding: 8px;
        border: none;
        border-bottom: solid #adadad 1px;
    }

    .table {
        width: 100%;
    }

    .table tbody tr th {
        width: 40%;
    }

    .table tbody tr td {
        width: 60%;
    }

    .table.table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    h3.form-subtitle {
        margin-top: 0px;
    }

    .form-acjf .personal-info {
        border-radius: 8px;
        border: solid #adadad 1px;
        padding: 20px;
        margin-bottom: 15px;
    }

    .form-acjf .form-row {
        width: 100%;
    }

    .form-acjf .form-row label {
        display: block;
        margin-bottom: 5px;
        font-size: small;
    }

    .form-acjf .form-row input,
    .form-acjf .form-row select,
    .form-acjf .form-row textarea {
        display: block;
        box-sizing: border-box;
        padding: 5px 8px;
        font-size: 18px;
        border-radius: 8px;
        border: solid #1d81bb 1px;
        margin-bottom: 20px;
        width: 100%;
    }

    .form-acjf button.submit {
        width: 100%;
        border-radius: 8px;
        background-color: rgb(35, 35, 121);
        border: none;
        color: #fff;
        font-size: 20px;
        padding: 8px 20px;
    }

    .form-acjf button {
        cursor: pointer;
        opacity: 1;
    }

    .form-acjf button:hover {
        opacity: 0.9;
    }
    .etp-2{
        display: none;
    }
    .pr-0{
        padding-right: 0px !important;
    }
    .pl-0{
        padding-left: 0px !important;
    }
</style>
<!-- Formulário de agendamento: -->
@php
    $arr_ddi = isset($dados['arr_ddi']) ? $dados['arr_ddi'] : [];
    $ddi_padrao = 55;
@endphp
<div class="row">
    <div class="col-12 mt-5 mb-5">
        <form class="form-acjf" action="/ajax/get-rab" id="form-agendamento" action="">
            <h3 class="form-subtitle">Preencha com suas informações de contato:</h3>
            <fieldset class="personal-info">
                <h6>Informações pessoais:</h6>
                <div class="form-row">
                    <label for="nome">Nome</label>
                    <input required type="nome" class="form-control" name="name" placeholder="Seu Nome">
                </div>
                <div class="form-row row mx-0">
                    <label for="whatsapp">Whatsapp</label>
                    <div class="col-md-3 col-4 pl-0 pr-0">
                        <select required class="form-control" name="config[ddi]">
                            @foreach ($arr_ddi as $k=>$v )
                                <option value="{{$v['ddi']}}" @if($v['ddi']==$ddi_padrao) selected @endif>{!!$v['pais']!!} +{{$v['ddi']}} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9 col-8 pl-0 pr-0">
                        <input required class="form-control" type="text" onblur="mask(this,clientes_mascaraTelefone);" onkeypress="mask(this,clientes_mascaraTelefone);" name="config[whatsapp]" placeholder="Seu Whatsapp">
                    </div>
                </div>
                <div class="form-row">
                    <label for="email">E-mail</label>
                    <input required type="email" name="email" class="form-control" placeholder="Seu melhor email">
                </div>
                <div class="form-row etp-1">
                    <label for="email">Matrícula da Aeronave</label>
                    <input required type="text" maxlength="5" style="text-transform:uppercase" class="form-control" onchange="atualizarConsulta()" name="matricula" placeholder="xxxxx">
                    <input type="hidden" id="config_consulta" name="config[consulta]" value="">
                    <input type="hidden" id="config_matricula" name="config[matricula]">
                    <input type="hidden" id="token" name="token" value="{{uniqid()}}">
                </div>
                <!-- Retorno da pesquisa: -->
                <div class="form-row retorno-pesquisa d-none">
                    <h2 class="titulo-retorno">Matrícula <b id="matricula">PRHNA</b></h2>
                </div>
                <div class="form-row etp-2">
                    <label for="servicos">Tipo de manutenção</label>
                    <select required name="config[servicos]">
                        <!-- Lista de serviços que virá do sistema -->
                        <option value="">...</option>
                        <option value="Manutenção de 50h">Manutenção de 50h</option>
                        <option value="Manutenção de 100h">Manutenção de 100h</option>
                        <option value="Manutenção de 200h">Manutenção de 200h</option>
                        <option value="Manutenção de 500h">Manutenção de 500h</option>
                        <option value="Manutenção de 1000h">Manutenção de 1000h</option>
                        <option value="CVA">CVA</option>
                        <option value="Inspeção de aeronavegabioptiondade">Inspeção de aeronavegabioptiondade</option>
                        <option value="Inspeção pré-compra">Inspeção pré-compra</option>
                        <option value="Pane">Pane</option>
                        <option value="Outros">Outros</option>

                    </select>
                </div>

                <!-- Se no campo anterior o usuário escolher a opção "outros", o campo abaixo estará disponível! -->
                <div class="form-row etp-2">
                    <label for="obs">Descreva o serviço</label>
                    <textarea name="obs"></textarea>
                    @csrf
                </div>
                <div class="form-row etp-2">
                    <label for="contrato">
                        <input type="checkbox" required name="meta[termo]" value="s" id="contrato"> Aceito o <a href="{{ url('/'.App\Qlib\Qlib::get_slug_post_by_id(10))}}" target="_BLANK">termo de uso</a>
                    </label>
                </div>
                <div class="form-row mens">
                </div>
                </fieldset>
            <button class="submit etp-1" btn="permanecer" type="submit">Avançar</button>
            <button class="submit etp-2 sub-2" btn="sair" type="submit">Enviar agendamento</button>
        </form>
    </div>
</div>
<script>

    function atualizarConsulta(){
        $('[name="config[consulta]"]').val('');
    }
    $(function(){
        $('#form-agendamento button.etp-1').on('click',function(e){
            e.preventDefault();
            let btn_press = $(this).attr('btn');
            submitFormulario($('#form-agendamento'),function(res){
                lib_formatMensagem_front('.mens',res.mens,res.color);
                if(res.exec){
                    if(res.data && res.consulta){
                        render_tabela_rab(res.data,res.consulta);
                        $('#form-agendamento').attr('action','/ajax/enviar-agendamento')
                        submitEtp2();
                    }
                }
                if(btn_press=='sair'){
                    if(pop){
                            window.opener.popupCallback_vinculo(res); //Call callback function
                            window.close(); // Close the current popup
                            return;
                    }
                    var redirect = $('[btn-volter="true"]').attr('redirect');

                    if(redirect){
                        if(pop){
                            window.opener.popupCallback(function(){
                                alert('pop some data '+redirect);
                            }); //Call callback function
                            window.close(); // Close the current popup
                            return;
                        }else{
                            window.location = redirect;
                        }
                    }else if(res.return){
                        if(pop){
                            window.opener.popupCallback(function(){
                                alert('pop some data '+res.return);
                            }); //Call callback function
                            window.close(); // Close the current popup
                            return;
                        }else{
                            window.location = res.return;
                        }
                    }
                }else if(btn_press=='permanecer'){
                    if(res.redirect){
                        window.location = res.redirect;
                    }
                }
                if(res.errors){
                    alert('erros');
                    console.log(res.errors);
                }
            });
        });

    });
    $('.sub-2').on('click',function(e){
        e.preventDefault();
        let btn_press = $(this).attr('btn');
        submitFormulario($('#form-agendamento'),function(res){
            if(res.mens){
                lib_formatMensagem_front('.mens',res.mens,res.color);
            }
            // if(d=res.data){
            //     render_tabela_rab(d);
            // }
            if(btn_press=='sair'){
                // if(pop){
                //         window.opener.popupCallback_vinculo(res); //Call callback function
                //         window.close(); // Close the current popup
                //         return;
                // }
                // var redirect = $('[btn-volter="true"]').attr('redirect');

                // if(redirect){
                //     window.location = redirect;
                // }else
                if(res.return){
                    window.location = res.return;
                }
            }else if(btn_press=='permanecer'){
                if(res.redirect){
                    window.location = res.redirect;
                }
            }
            if(res.errors){
                alert('erros');
                console.log(res.errors);
            }
        });
    });
    function submitEtp2(val){
        if(val=='remove'){
            $('[name="api"]').remove();
        }else{
            $('[name="api"]').remove();
            var imp = '<input type="hidden" name="api" value="false" />';
            $(imp).insertAfter('[name="matricula"]');
        }
    }

</script>
<!-- Shortcode: END -->
