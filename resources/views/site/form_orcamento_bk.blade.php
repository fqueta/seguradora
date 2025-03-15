<!-- Shortcode: START -->

<style>
    .d-none {
        display: none;
    }

    .form-acjf .form-acjf-search {
        display: flex;
    }

    .form-acjf .form-acjf-search input {
        width: 100%;
        padding: 5px 8px;
        font-size: 18px;
        border-radius: 8px 0 0 8px;
        border: solid #1d81bb 1px;
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
        margin-top: 40px;
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
</style>
<form class="form-acjf" id="busca-rab" action="/ajax/get-rab">
    <div class="form-acjf-search">
        <input required type="text" maxlength="5" name="matricula" placeholder="Busque pela matrícula">
        @csrf
        <button type="submit" btn="permanecer"><i class="fa fa-search"></i>Buscar</button>
    </div>
</form>

<!-- Retorno da pesquisa: -->
<div class="retorno-pesquisa d-none">
    <h2 class="titulo-retorno">Matrícula <b id="matricula">PRHNA</b></h2>
    {{-- <table class="table table-hover">
        <tbody>
            <tr>
                <th id="proprietario" scope="row">Proprietário:</th>
                <td>SBXL LOCADORA DE AERONAVES LTDA</td>
            </tr>
            <tr>
                <th id="doc_proprietario" scope="row">CPF/CNPJ:</th>
                <td>48191596000180</td>
            </tr>
            <tr>
                <th id="cota" scope="row">Cota Parte %:</th>
                <td>100</td>
            </tr>
            <tr>
                <th id="data_compra" scope="row">Data da Compra/Transferência:</th>
                <td>19/10/22</td>
            </tr>
            <tr>
                <th id="operador" scope="row">Operador:</th>
                <td>AEROCLUBE DE JUIZ DE FORA</td>
            </tr>
            <tr>
                <th id="doc_operador" scope="row">CPF/CNPJ:</th>
                <td>21616420000177</td>
            </tr>
            <tr>
                <th id="fabricante" scope="row">Fabricante:</th>
                <td>CESSNA AIRCRAFT</td>
            </tr>
            <tr>
                <th id="ano_fabricacao" scope="row">Ano de Fabricação:</th>
                <td>1977</td>
            </tr>
            <tr>
                <th id="modelo" scope="row">Modelo:</th>
                <td>152</td>
            </tr>
            <tr>
                <th id="num_serie" scope="row">Número de Série:</th>
                <td>15281007</td>
            </tr>
            <tr>
                <th id="icao" scope="row">Tipo ICAO:</th>
                <td>C152</td>
            </tr>
            <tr>
                <th id="homologacao" scope="row">Categoria de Homologação:</th>
                <td>UTILIDADE</td>
            </tr>
            <tr>
                <th id="tipo_habilitacao" scope="row">Tipo de Habilitação para Pilotos:</th>
                <td>MNTE</td>
            </tr>
            <tr>
                <th id="classe" scope="row">Classe da Aeronave:</th>
                <td>POUSO CONVECIONAL 1 MOTOR CONVENCIONAL</td>
            </tr>
            <tr>
                <th id="peso_max" scope="row">Peso Máximo de Decolagem:</th>
                <td>757 - Kg</td>
            </tr>
            <tr>
                <th id="num_passageiros" scope="row">Número de Passageiros:</th>
                <td>001</td>
            </tr>
            <tr>
                <th id="tipo_voo_autorizado" scope="row">Tipo de voo autorizado:</th>
                <td>VFR Noturno</td>
            </tr>
            <tr>
                <th id="tripulacao" scope="row">Tripulação Mínima prevista na Certificação:</th>
                <td>1</td>
            </tr>
            <tr>
                <th id="num_assentos" scope="row">Número de Assentos:</th>
                <td>2</td>
            </tr>
            <tr>
                <th id="cat_registro" scope="row">Categoria de Registro:</th>
                <td>PRIVADA INSTRUCAO</td>
            </tr>
            <tr>
                <th id="num_matricula" scope="row">Número da Matrícula:</th>
                <td>20879</td>
            </tr>
            <tr>
                <th id="status" scope="row">Status da Operação:</th>
                <td>OPERAÇÃO NEGADA PARA TÁXI AÉREO</td>
            </tr>
            <tr>
                <th id="gravame" scope="row">Gravame:</th>
                <td>ARRENDAMENTO OPERACIONAL</td>
            </tr>
            <tr>
                <th id="cva_val" scope="row">Data de Validade do CVA:</th>
                <td>23/01/25</td>
            </tr>
            <tr>
                <th id="aeronavegabilidade" scope="row">Situação de Aeronavegabilidade:</th>
                <td>SITUAÇÃO NORMAL</td>
            </tr>
            <tr>
                <th id="motivo" scope="row">Motivo(s):</th>
                <td></td>
            </tr>
        </tbody>
    </table> --}}
</div>

<!-- Formulário de agendamento: -->
<form class="form-acjf d-none" action="/ajax/enviar-agendamento" id="form-agendamento" action="">
    <input required type="hidden" id="config_matricula" name="config[matricula]">
    <input required type="hidden" id="config_consulta" name="config[consulta]">

    <h3 class="form-subtitle">Preencha com sua informações de contato:</h3>

    <fieldset class="personal-info">
        <h6>Informações pessoais:</h6>
        <div class="form-row">
            <label for="whatsapp">Whatsapp</label>
            <input required type="text" onblur="mask(this,clientes_mascaraTelefone);" onkeypress="mask(this,clientes_mascaraTelefone);" name="whatsapp" placeholder="Seu Whatsapp">
        </div>
        <div class="form-row">
            <label for="email">E-mail</label>
            <input required type="email" name="email" placeholder="Seu melhor email">
        </div>
        <div class="form-row">
            <label for="servicos">Tipo de manutenção</label>
            <select required type="select" name="servicos">
                <!-- Lista de serviços que virá do sistema -->
                <option value="">...</option>
                <option value="">...</option>
                <option value="Outros">Outros</option>
            </select>
        </div>
        <!-- Se no campo anterior o usuário escolher a opção "outros", o campo abaixo estará disponível! -->
        <div class="form-row">
            <label for="obs">Descreva o serviço</label>
            <textarea name="obs"></textarea>
            @csrf
        </div>
    </fieldset>
    <button class="submit" btn="sair" type="submit">Enviar agendamento</button>
</form>
<script>
    $(function(){
        $('#busca-rab [type="submit"]').on('click',function(e){
            e.preventDefault();
            let btn_press = $(this).attr('btn');
            submitFormulario($('#busca-rab'),function(res){
                if(res.exec){
                    lib_formatMensagem('.mens',res.mens,res.color);
                }
                if(d=res.data){
                    render_tabela_rab(d);
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
        $('#form-agendamento [type="submit"]').on('click',function(e){
            e.preventDefault();
            let btn_press = $(this).attr('btn');
            submitFormulario($('#form-agendamento'),function(res){
                if(res.exec){
                    lib_formatMensagem('.mens',res.mens,res.color);
                }
                if(d=res.data){
                    render_tabela_rab(d);
                }
                if(btn_press=='sair'){
                    if(pop){
                            window.opener.popupCallback_vinculo(res); //Call callback function
                            window.close(); // Close the current popup
                            return;
                    }
                    var redirect = $('[btn-volter="true"]').attr('redirect');

                    if(redirect){
                        window.location = redirect;
                    }else if(res.return){
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
    });
    function render_tabela_rab(obj){
        try {
            var tem1 = '<table class="table"><thead><tr><th colspan="2">Informações da Aeronave</th></tr></thead><tbody>{tbody}</tbody></table>',tem2='<tr><td>{key}</td><td>{value}</td></tr>',tr='';
            if(typeof obj == 'object'){
                for (const [key, value] of Object.entries(d)) {
                    tr += tem2.replace('{key}',key);
                    tr = tr.replace('{value}',value);
                    if(key=='Matrícula'){
                        $('#config_matricula').val(value);
                    }
                    console.log(`${key}: ${value}`);
                }
                var ret = tem1.replace('{tbody}',tr),consulta = encodeArray(obj);
                $('.retorno-pesquisa').html(ret).removeClass('d-none');
                $('#form-agendamento').removeClass('d-none');
                $('#config_consulta').val(consulta);
            }
        } catch (error) {
            console.log(error);

        }

    }
</script>
<!-- Shortcode: END -->
