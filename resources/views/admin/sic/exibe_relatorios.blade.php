<div class="col-12">
    <h3 class="car-title" id="titulo-relatorio">{{$titulo}} - <small>{{$titulo2}}</small></h3>
</div>
<div class="col-12 text-center">
    {{__('Total de Usuários Cadastrados no Sistema')}}: <b> {{$d_rel['total_users']}} </b>
</div>
<div class="col-12 mb-2">
    @isset($campos_form_consulta)
    <form action="" method="get">
        <div class="row">
            @foreach ($campos_form_consulta as $k=>$v)
            {!!App\Qlib\Qlib::qForm([
                'type'=>@$v['type'],
                'campo'=>$k,
                'label'=>$v['label'],
                'placeholder'=>@$v['placeholder'],
                'ac'=>'alt',
                'value'=>isset($v['value'])?$v['value']: @$value[$k],
                'tam'=>@$v['tam'],
                'event'=>@$v['event'],
                'checked'=>@$value[$k],
                    'selected'=>@$v['selected'],
                    'arr_opc'=>@$v['arr_opc'],
                    'option_select'=>@$v['option_select'],
                    'class'=>@$v['class'],
                    'class_div'=>@$v['class_div'],
                    'rows'=>@$v['rows'],
                    'cols'=>@$v['cols'],
                    'data_selector'=>@$v['data_selector'],
                    'script'=>@$v['script'],
                    'valor_padrao'=>@$v['valor_padrao'],
                    'dados'=>@$v['dados'],
                    ])!!}
            @endforeach
        </div>
    </form>
    @endisset
</div>
@isset($totais_gerais)
    @foreach ($totais_gerais as $kt=>$vt)
    <div class="col-md-4 border-top">
        <div class="row">
            <div class="col-6">
                {{$vt['label']}}:
            </div>
            <div class="col-6 text-right">
                <span class="badge badge-primary">
                    {{$vt['value']}}
                </span>
            </div>
        </div>
    </div>
    @endforeach
@endisset
@isset($d_rel['grafico'])
     <!-- GRÁFICO PRINCIPAL -->
    <div class="col-md-12">
        <style type="text/css">
            .highcharts-figure,
            .highcharts-data-table table {
                min-width: 320px;
                max-width: 100%;
                margin: 1em auto;
            }

            .highcharts-data-table table {
                font-family: Verdana, sans-serif;
                border-collapse: collapse;
                border: 1px solid #ebebeb;
                margin: 10px auto;
                text-align: center;
                width: 100%;
                max-width: 500px;
            }

            .highcharts-data-table caption {
                padding: 1em 0;
                font-size: 1.2em;
                color: #555;
            }

            .highcharts-data-table th {
                font-weight: 600;
                padding: 0.5em;
            }

            .highcharts-data-table td,
            .highcharts-data-table th,
            .highcharts-data-table caption {
                padding: 0.5em;
            }

            .highcharts-data-table thead tr,
            .highcharts-data-table tr:nth-child(even) {
                background: #03973c;
            }

            .highcharts-data-table tr:hover {
                background: #f1f7ff;
            }

            input[type="number"] {
                min-width: 50px;
            }
            .highcharts-credits{
                display: none;
            }
        </style>
        <script src="{{URL::to('/')}}/js/charts/highcharts.js"></script>
        <script src="{{URL::to('/')}}/js/charts/modules/exporting.js"></script>
        <script src="{{URL::to('/')}}/js/charts/modules/export-data.js"></script>
        <script src="{{URL::to('/')}}/js/charts/modules/accessibility.js"></script>
        <figure class="highcharts-figure">
            <div id="container"></div>
         </figure>
        <script type="text/javascript">
            // Build the chart
            Highcharts.chart('container', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '{{__('Porcentagem Estatística')}}'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    name: '{{__('Total')}}',
                    colorByPoint: true,
                    data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico'])!!}
                }]
            });
        </script>
    </div>
     <!--FIM GRÁFICO PRINCIPAL -->
@endisset
@isset($d_rel['grafico_assunto'])
<!-- ASSUNTO DOS PEDIDOS -->
<div id="conteudo_grafico_assuntos" class="conteudo_grafico col-md-12 mb-2">
    <figure class="highcharts-figure">
        <div id="container_grafico_assuntos" class="container_grafico"></div>
    </figure>

    <script>
        Highcharts.chart('container_grafico_assuntos', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Assunto dos Pedidos'
            },
            tooltip: {
                pointFormat: '{series.name}: <strong>{point.percentage:.1f}%</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: "{{__('Percentual')}}",
                colorByPoint: true,
                data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_assunto'])!!}
            }]
        });
    </script>
</div>
   <!-- FIM ASSUNTO DOS PEDIDOS -->
@endisset
@isset($d_rel['grafico_assunto'])
<!-- MOTIVOS DE NEGATIVA DE RESPSOTAS -->
    {{-- <div class="sic_titulo_relatorio sw_lato_bold col-md-12">
        <span class="swfa fas fa-minus-circle fa-lg icone"></span> Motivos de Negativa de Respostas

        <div class="sic_area_botao">
            <div class="sic_texto_btns sw_lato">Visualizar dados em: </div>

            <div class="sic_botao btn_visualizar_dados sw_txt_tooltip" title="Visualizar tabela" id="btn_tabela_motivos" data-id="motivos" data-tipo="tabela"><span id="icone_botao_motivos" class="swfa fas fa-table"></span></div>

            <div class="sic_botao sic_botao_desabilitado btn_visualizar_dados sw_txt_tooltip" title="Visualizar gráfico" id="btn_grafico_motivos" data-id="motivos" data-tipo="grafico"><span id="icone_botao_motivos" class="swfa fas fa-chart-pie"></span></div>
        </div>
    </div> --}}

<!-- TABELA -->
{{-- <div id="conteudo_tabela_motivos" class="col-md-12">
    <table class="display tabela" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Motivos</th>
                <th>Quantidade</th>
                <th>Porcentagem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dados Pessoais </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Informação sigilosa conforme LAI </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Informação sigilosa legislação específica </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Pedido exige tratamento adicional de dados </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Pedido Genérico </td>
                <td align="center">1</td>
                <td align="center">100,00%</td>
            </tr>
                                <tr>
                <td>Pedido Incompreensível </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Processo decisório em curso </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td>Outro </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                <tr>
                <td><strong class="total"><i class="swfa fas fa-plus-square"></i>&nbsp;TOTAL </strong></td>
                <td align="center"><strong class="total">1</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div> --}}


<!-- GRÁFICO -->
<div id="conteudo_grafico_motivos" class="conteudo_grafico col-md-12 mb-2">
    <figure class="cont_grafico">
        <div id="container_grafico_motivos" class="container_grafico"></div>
    </figure>

    <script>
        Highcharts.chart('container_grafico_motivos', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Motivos de Negativa de Respostas'
            },
            tooltip: {
                pointFormat: '{series.name}: <strong>{point.percentage:.1f}%</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: "{{__('Percentual')}}",
                colorByPoint: true,
                data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_motivo'])!!}
            }]
        });
    </script>
</div>
<!-- FIM MOTIVOS DE NEGATIVA DE RESPSOTAS -->
@endisset
<!-- TIPO DE SOLICITANTES -->
{{-- <div class="sic_titulo_relatorio sw_lato_bold">
    <span class="swfa fas fa-users fa-lg icone"></span> Tipo de Solicitantes

    <div class="sic_area_botao">
        <div class="sic_texto_btns sw_lato">Visualizar dados em: </div>

        <div class="sic_botao btn_visualizar_dados sw_txt_tooltip" title="Visualizar tabela" id="btn_tabela_solicitantes" data-id="solicitantes" data-tipo="tabela"><span id="icone_botao_solicitantes" class="swfa fas fa-table"></span></div>

        <div class="sic_botao sic_botao_desabilitado btn_visualizar_dados sw_txt_tooltip" title="Visualizar gráfico" id="btn_grafico_solicitantes" data-id="solicitantes" data-tipo="grafico"><span id="icone_botao_solicitantes" class="swfa fas fa-chart-pie"></span></div>
    </div>
</div> --}}

<!-- TABELA -->
{{-- <div id="conteudo_tabela_solicitantes">
    <table class="display tabela" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Porcentagem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pessoa Física </td>
                <td align="center">22</td>
                <td align="center">88,00%</td>
            </tr>
                                <tr>
                <td>Pessoa Jurídica </td>
                <td align="center">3</td>
                <td align="center">12,00%</td>
            </tr>
                                <tr>
                <td><strong class="total"><i class="swfa fas fa-plus-square"></i>&nbsp;TOTAL </strong></td>
                <td align="center"><strong class="total">25</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div> --}}

<!-- GRÁFICO -->
<div id="conteudo_grafico_solicitantes" class="conteudo_grafico col-md-12 mb-2">
    <figure class="cont_grafico">
        <div id="container_grafico_solicitantes" class="container_grafico"></div>
    </figure>

    <script>
        Highcharts.chart('container_grafico_solicitantes', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Tipo de Solicitantes'
            },
            tooltip: {
                pointFormat: '{series.name}: <strong>{point.percentage:.1f}%</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: "{{__('Percentual')}}",
                colorByPoint: true,
                data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_solicitante'])!!}
            }]
        });
    </script>
</div>
<!-- FIM TIPO DE SOLICITANTES -->
 <!-- PERFIL DOS SOLICITANTES PESSOA FÍSICA -->
 {{-- <div class="sic_titulo_relatorio sw_lato_bold">
    <span class="swfa fas fa-user fa-lg icone"></span> Perfil dos Solicitantes Pessoa Física

    <div class="sic_area_botao">
        <div class="sic_texto_btns sw_lato">Visualizar dados em: </div>

        <div class="sic_botao btn_visualizar_dados sw_txt_tooltip" title="Visualizar tabela" id="btn_tabela_perfil_pf" data-id="perfil_pf" data-tipo="tabela"><span id="icone_botao_perfil_pf" class="swfa fas fa-table"></span></div>

        <div class="sic_botao sic_botao_desabilitado btn_visualizar_dados sw_txt_tooltip" title="Visualizar gráfico" id="btn_grafico_perfil_pf" data-id="perfil_pf" data-tipo="grafico"><span id="icone_botao_perfil_pf" class="swfa fas fa-chart-pie"></span></div>
    </div>
</div> --}}

<!-- TABELA -->
{{-- <div id="conteudo_tabela_perfil_pf">
    <table class="display tabela" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Gênero</th>
                <th>Quantidade</th>
                <th>Porcentagem</th>
            </tr>
        </thead>
        <tbody>
                                            <tr>
                    <td>Feminino </td>
                    <td align="center">6</td>
                    <td align="center">24,00%</td>
                </tr>
                                                <tr>
                    <td>Masculino </td>
                    <td align="center">17</td>
                    <td align="center">68,00%</td>
                </tr>
                                                <tr>
                    <td>Não Informado </td>
                    <td align="center">2</td>
                    <td align="center">8,00%</td>
                </tr>
                                                <tr>
                    <td><strong><i class="swfa fas fa-plus-square"></i>&nbsp;TOTAL </strong></td>
                    <td align="center"><strong>25</strong></td>
                    <td></td>
                </tr>



                <tr>
                    <td><strong class="cabecalho2">ESCOLARIDADE</strong></td>
                    <td align="center"></td>
                    <td align="center"></td>
                </tr>

            </tbody>
    </table>
</div> --}}


<!-- GRÁFICO -->
<div id="conteudo_grafico_perfil_pf" class="conteudo_grafico col-md-12 mb-2">
    <figure class="cont_grafico">
        <div id="container_grafico_perfil_pf" class="container_grafico"></div>
    </figure>

    <script>
        Highcharts.chart("container_grafico_perfil_pf", {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: "pie",
            },
            title: {
                text: "Gênero",
            },
            tooltip: {
                pointFormat: "{series.name}: <strong>{point.percentage:.1f}%</strong>",
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: "pointer",
                    dataLabels: {
                        enabled: false,
                    },
                    showInLegend: true,
                },
            },
            series: [
                {
                    name: "{{__('Percentual')}}",
                    colorByPoint: true,
                    data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_genero'])!!},
                },
            ],
        });
    </script>

    <figure class="cont_grafico">
        <div id="container_grafico_perfil_pf_escolaridade" class="container_grafico"></div>
    </figure>
    <script>
        Highcharts.chart("container_grafico_perfil_pf_escolaridade", {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: "pie",
            },
            title: {
                text: "Escolaridade",
            },
            tooltip: {
                pointFormat: "{series.name}: <strong>{point.percentage:.1f}%</strong>",
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: "pointer",
                    dataLabels: {
                        enabled: false,
                    },
                    showInLegend: true,
                },
            },
            series: [
                {
                    name: "{{__('Percentual')}}",
                    colorByPoint: true,
                    data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_escolaridade'])!!},
                },
            ],
        });
    </script>

    <div class="cont_grafico">
        <div id="container_grafico_perfil_pf_profissao" class="container_grafico"></div>
    </div>

    <script>
        Highcharts.chart("container_grafico_perfil_pf_profissao", {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: "pie",
            },
            title: {
                text: "Profissão",
            },
            tooltip: {
                pointFormat: "{series.name}: <strong>{point.percentage:.1f}%</strong>",
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: "pointer",
                    dataLabels: {
                        enabled: false,
                    },
                    showInLegend: true,
                },
            },
            series: [
                {
                    name: "{{__('Percentual')}}",
                    colorByPoint: true,
                    data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_profissao'])!!},
                },
            ],
        });
    </script>
</div>
<!-- PERFIL DOS SOLICITANTES PESSOA JURÍDICA -->
{{-- <div class="sic_titulo_relatorio sw_lato_bold">
    <span class="swfa fas fa-university fa-lg icone"></span> Perfil dos Solicitantes Pessoa Jurídica

    <div class="sic_area_botao">
        <div class="sic_texto_btns sw_lato">Visualizar dados em: </div>

        <div class="sic_botao btn_visualizar_dados sw_txt_tooltip" title="Visualizar tabela" id="btn_tabela_perfil_pj" data-id="perfil_pj" data-tipo="tabela"><span id="icone_botao_perfil_pj" class="swfa fas fa-table"></span></div>

        <div class="sic_botao sic_botao_desabilitado btn_visualizar_dados sw_txt_tooltip" title="Visualizar gráfico" id="btn_grafico_perfil_pj" data-id="perfil_pj" data-tipo="grafico"><span id="icone_botao_perfil_pj" class="swfa fas fa-chart-pie"></span></div>
    </div>
</div> --}}

<!-- TABELA -->
{{-- <div id="conteudo_tabela_perfil_pj">
    <table class="display tabela" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Tipo Pessoa Jurídica</th>
                <th>Quantidade</th>
                <th>Porcentagem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Empresa - PME </td>
                <td align="center">2</td>
                <td align="center">66,67%</td>
            </tr>
                                            <tr>
                <td>Instituição de Ensino e/ou Pesquisa </td>
                <td align="center">1</td>
                <td align="center">33,33%</td>
            </tr>
                                            <tr>
                <td>Veículo de Comunicação </td>
                <td align="center">0</td>
                <td align="center">0,00%</td>
            </tr>
                                            <tr>
                <td><strong class="total"><i class="swfa fas fa-plus-square"></i>&nbsp;TOTAL </strong></td>
                <td align="center"><strong class="total">3</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>  --}}

<!-- GRÁFICO -->
<div id="conteudo_grafico_perfil_pj" class="conteudo_grafico col-md-12 mb-2">
    <figure class="cont_grafico">
        <div id="container_grafico_perfil_pj" class="container_grafico"></div>
    </figure>

    <script>
        Highcharts.chart('container_grafico_perfil_pj', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Tipo de Pessoa Jurídica'
            },
            tooltip: {
                pointFormat: '{series.name}: <strong>{point.percentage:.1f}%</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: "{{__('Percentual')}}",
                colorByPoint: true,
                data: {!!App\Qlib\Qlib::lib_array_json($d_rel['grafico_tipo_pj'])!!}
            }]
        });
    </script>
</div>
<!-- FIM PERFIL DOS SOLICITANTES PESSOA JURÍDICA -->

<!-- FIM PERFIL DOS SOLICITANTES PESSOA FÍSICA -->
