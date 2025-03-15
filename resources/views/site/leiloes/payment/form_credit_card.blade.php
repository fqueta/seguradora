<div class="card">
    <div class="card-header">
        <i class="fa fa-credit-card"></i> Informações do cartão de crédito
    </div>
    <div class="card-body">
        <div class="pn-cred_card">
            <div class="col-md-12 d-none">
                <label><input type="checkbox" value="outro" name="cartao[dono]" /> Usar cartão de outra pessoa</label>
            </div>
            <div class="col-md-12 m-bp" div-id="cartao[numero_cartao]">
                <label class="" data-toggle="tooltip" title="" for="cartao[numero_cartao]">Numero do cartão de crédito*</label>
                <input data-placement="right" id="cartao[numero_cartao]" width="100%" name="cartao[numero_cartao]" placeholder="" class="form-control c-cred_card" value="" type="tel" style="" required="" />
            </div>
            <div class="col-md-12 m-bp" div-id="cartao[nome_no_cartao]">
                <label data-toggle="tooltip" title="Este é o nome do titular, que está impresso no cartão" for="cartao[nome_no_cartao]">Nome no cartão*</label>
                <input data-placement="right" id="cartao[nome_no_cartao]" width="100%" name="cartao[nome_no_cartao]" placeholder="" class="form-control c-cred_card" value="" type="text" style="" required="" />
            </div>
            <input id="compra[id_leilao]" name="compra[id_leilao]" value="{{$id_leilao}}" type="hidden" />
            <div class="row pl-3 pr-3">
                <div class="col-md-12" id="campo_validade"><label>Data de validate:</label></div>
                <div class="col-6">
                    @if(isset($arr_mes) && is_array($arr_mes))
                        <select class="select-mes form-control form-control c-cred_card" name="cartao[validade_mes]" required="">
                            <option value="" selected="">Mês</option>
                            @foreach ($arr_mes as $km=>$vm)
                                <option value="{{$km}}">{{$vm}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-6">
                    @if(isset($arr_ano) && is_array($arr_ano))
                        <select class="select-ano form-control form-control c-cred_card" name="cartao[validade_ano]" required="">
                            <option value="" selected="">Ano</option>
                            @foreach ($arr_ano as $ka=>$va)
                                <option value="{{$ka}}">{{$va}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
            <div class="col-md-12 m-bp" div-id="cartao[codigo_seguranca]">
                <label class="" data-toggle="tooltip" title="" for="cartao[codigo_seguranca]">Código de serguraça (CVV)*</label>
                <input data-placement="right" id="cartao[codigo_seguranca]" width="100%" name="cartao[codigo_seguranca]" placeholder="" class="form-control c-cred_card" value="" type="tel" style="" required="" />
            </div>

            <div class="col-md-12" id="valor_compra">
                @if(isset($arr_valores) && is_array($arr_valores))
                <label>Valor: </label><br />
                <select class="select-valor form-control form-control c-cred_card" name="cartao[valor]" required="">
                    @foreach ($arr_valores as $kv=>$vv)
                        <option value="{{$kv}}">{{$vv}}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
        <style>
            .pn-cad_responsavel .header {
                font-size: 16px;
                line-height: 1.5;
                border-bottom: 1px solid rgba(0, 0, 0, 0.12);
                margin-top: 16px;
                margin-bottom: 16px;
                font-weight: bold;
            }
            .pn-btn {
                padding-top: 10px;
            }
            .btn-voltar,
            .btn-continuar,
            .pn-cad_responsavel {
                display: none;
            }
        </style>
        <span class="pn-cad_responsavel">
            <div class="row">
                <div class="col-md-12 header marg-t-16">Informações do titular do cartão</div>
                <div class="col-md-12 m-bp" div-id="dados[responsavel][Nome]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Nome]">Nome completo*</label>
                    <input data-placement="right" id="dados[responsavel][Nome]" width="100%" name="dados[responsavel][Nome]" placeholder="" class="form-control input-md" value="" type="text" style="" required="" mark_reponse="" disabled="" />
                </div>
                <div class="col-md-12 m-bp" div-id="dados[responsavel][Email]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Email]">Email*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Email]"
                        width="100%"
                        name="dados[responsavel][Email]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="email"
                        style=""
                        required=""
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                {{-- <input id="compra[id_leilao]" name="compra[id_leilao]" value="2" type="hidden" /> --}}
                <div class="col-md-6 m-bp" div-id="dados[responsavel][Cpf]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Cpf]">Cpf*</label>
                    <input data-placement="right" id="dados[responsavel][Cpf]" width="100%" name="dados[responsavel][Cpf]" placeholder="" class="form-control input-md" value="" type="text" style="" required="" mark_reponse="" disabled="" />
                </div>
                <div class="col-md-6 m-bp" div-id="dados[responsavel][Celular]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Celular]">Celular*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Celular]"
                        width="100%"
                        name="dados[responsavel][Celular]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-12 header marg-t-16">Informações de endereço</div>
                <div class="col-md-3 m-bp" div-id="dados[responsavel][Cep]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Cep]">Cep*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Cep]"
                        width="100%"
                        name="dados[responsavel][Cep]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        quet-acao="cep"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-7 m-bp" div-id="dados[responsavel][Endereco]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Endereco]">Endereco*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Endereco]"
                        width="100%"
                        name="dados[responsavel][Endereco]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        q-inp="endereco"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-2 m-bp" div-id="dados[responsavel][Numero]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Numero]">Numero*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Numero]"
                        width="100%"
                        name="dados[responsavel][Numero]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        q-inp="numero"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-4 m-bp" div-id="dados[responsavel][Bairro]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Bairro]">Bairro*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Bairro]"
                        width="100%"
                        name="dados[responsavel][Bairro]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        q-inp="bairro"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-3 m-bp" div-id="dados[responsavel][Compl]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Compl]">Compl</label>
                    <input data-placement="right" id="dados[responsavel][Compl]" width="100%" name="dados[responsavel][Compl]" placeholder="" class="form-control input-md" value="" type="text" style="" mark_reponse="" disabled="" />
                </div>
                <div class="col-md-3 m-bp" div-id="dados[responsavel][Cidade]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Cidade]">Cidade*</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Cidade]"
                        width="100%"
                        name="dados[responsavel][Cidade]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        q-inp="cidade"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <div class="col-md-2 m-bp" div-id="dados[responsavel][Uf]">
                    <label class="" data-toggle="tooltip" title="" for="dados[responsavel][Uf]">UF</label>
                    <input
                        data-placement="right"
                        id="dados[responsavel][Uf]"
                        width="100%"
                        name="dados[responsavel][Uf]"
                        placeholder=""
                        class="form-control input-md"
                        value=""
                        type="text"
                        style=""
                        required=""
                        q-inp="uf"
                        mark_reponse=""
                        disabled=""
                    />
                </div>
                <input id="dados[responsavel][token]" name="dados[responsavel][token]" value="5ea384c9a0b05" type="hidden" /><input id="dados[responsavel][campo_bus]" name="dados[responsavel][campo_bus]" value="token" type="hidden" />
                <input id="dados[responsavel][tab]" name="dados[responsavel][tab]" value="cmVzcG9uc2F2ZWw=" type="hidden" /><input id="dados[responsavel][ac]" name="dados[responsavel][ac]" value="cad" type="hidden" />
                <input id="dados[responsavel][conf]" name="dados[responsavel][conf]" value="s" type="hidden" /><input id="dados[responsavel][type_alt]" name="dados[responsavel][type_alt]" value="1" type="hidden" />
            </div>
        </span>
    </div>
</div>
