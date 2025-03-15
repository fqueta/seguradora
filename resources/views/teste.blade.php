
@php
    $tipo_pagina='secundaria';
@endphp
@extends('site.layout.app')
@section('main')

    <div class="container">
        <div class="row">
            <style>
                    table {
                        caption-side: bottom;
                        border-collapse: collapse;
                    }
                    .table-c td{
                        border: solid 1px #333;
                    }
                    .table-c th{
                        border: solid 2px #ccc;
                    }
                    .table-c tbody td{
                        border: solid 1px #ccc;
                    }
                    .table-c th,.table-c td{
                        text-align: center;
                    }
                    .table-c thead th{
                        font-size: 13px;
                    }
                    .table-c tbody th,.table-c tbody td{
                        font-size: 11px;
                    }
                    p{
                        text-align: justify;
                    }
                    h1{
                        font-size: 20px;
                        font-weight: bold;
                    }
                    h4{
                        font-size: 17px;
                    }
            </style>
            <h1 style="text-align: center">Termo de Ciência</h1>
            <h4 style="text-align: center">Ciência sobre Pagamento de Serviços de Manutenção Aeronáutica </h4>
            <ol style="font-weight: bold">
                <li>
                <b>Agendamento Prévio:</b>
                </li>
            </ol>
            <p>
                Todas as aeronaves que necessitarem de serviços de manutenção devem realizar um agendamento prévio. A falta de agendamento poderá acarretar cobranças adicionais pela utilização de espaços de hangar de terceiros, conforme as tarifas estabelecidas pelo proprietário do hangar.
            </p>
            <ol style="font-weight: bold" start="2">
                <li>
                    <b>
                        Documentação da Aeronave:
                    </b>
                </li>
            </ol>
            <p style="font-weight: normal;">
            Nos dias atuais, todos os trâmites de documentação entre oficinas de manutenção e a ANAC são realizados de forma digital. Essa transformação inclui a substituição da antiga IAM (Inspeção Anual de Manutenção) pelo CVA
                (Certificado de Verificação de Aeronavegabilidade), que evoluiu para o formato eletrônico, o e-CVA. Apesar dessa modernização, muitas aeronaves ainda não possuem sua documentação digitalizada, o que pode gerar atrasos e
                inconsistências na análise de itens vencidos ou próximos do vencimento, especialmente em situações que demandam uma revisão detalhada, como a preparação para um CVA.
            </p>
            <p style="font-weight: normal;">
                A digitalização da documentação é essencial para evitar atrasos no processo de orçamento e execução de serviços, garantindo que todos os registros sejam mantidos de forma segura, organizada e em backups digitais. Isso não apenas
                evita a perda de documentos, mas também assegura a rastreabilidade dos serviços realizados na aeronave, um requisito fundamental para a manutenção da aeronavegabilidade.
            </p>
            <p style="font-weight: normal;">
                Com isso em mente, a oficina estabelece que, ao receber uma aeronave cuja documentação não esteja em formato digital, será realizada a digitalização completa tanto da documentação atual quanto da histórica. Este serviço será
                cobrado à parte e terá o valor fixo de R$ 1.500,00, pagos no ato do recebimento da documentação.
            </p>
            <p style="font-weight: normal;">
                Essa prática visa alinhar os processos da aeronave ao padrão digital já adotado pela ANAC e pela oficina, contribuindo para uma gestão mais eficiente e segura da documentação e manutenção da aeronave.
            </p>
            <p style="font-weight: normal;">
                Ao receber uma aeronave cuja documentação não esteja digitalizada, a oficina realiza a digitalização completa de todos os documentos, assegurando sua preservação e rastreabilidade. No entanto, caso durante o processo de análise
                da documentação seja constatada a ausência de mapas técnicos essenciais para o Controle Técnico de Manutenção (CTM), será necessário um serviço mais amplo do que a simples digitalização.
            </p>
            <p style="font-weight: normal;">
                Neste caso, a oficina deverá produzir e organizar todos os mapas técnicos necessários para a continuidade e regularização do programa de manutenção da aeronave, incluindo: Controle de Componente, FCDAs, SIDs, CAPs, mapa de
                céula, motor e hélice, atualização de cadernetas (célula, motor e hélice) e outros documentos técnicos necessários o controle e a gestão técnica de itens controláveis.
            </p>
            <p>
                Este serviço adicional será cobrado à parte, com base na complexidade e no volume do trabalho envolvido. O cálculo será feito de acordo com o valor base da hora-homem de R$ 275,00/h, com um limite máximo de R$ 6.000,00.
            </p>
            <ol style="font-weight: bold" start="3">
                <li>
                    <b>
                        Orçamento e Aprovação:
                    </b>
                </li>
            </ol>
            <p>
                A oficina se compromete a receber a aeronave e elaborar um orçamento no menor prazo possível, que será enviado para aprovação no e-mail do cliente. Após a entrega deste orçamento, o cliente terá 48 horas úteis para decidir se aceita ou
                rejeita o plano de serviços proposto.
            </p>
            <p>
                <b>Aprovação do Orçamento:</b> Caso o orçamento seja aprovado dentro das 48 horas, não será cobrada taxa de hangaragem até que os serviços estejam completamente finalizados.
            </p>
            <p>
                <b>Rejeição do Orçamento:</b> Se o orçamento não for respondido ou for explicitamente rejeitado dentro do prazo de 48 horas, interpretaremos isso como uma recusa. Consequentemente, será cobrada a taxa de hangaragem desde o dia em que a
                aeronave chegou até o dia em que for retirada da oficina.
            </p>
            <ol style="font-weight: bold" start="4">
                <li>
                <b>
                    Custos de Serviços e Pagamento:
                </b>
                </li>
            </ol>
            <p>
            Todos os serviços recorrentes possuem valores fixos conforme tabela de serviços. Para iniciar a manutenção, o pagamento de 100% do valor do serviço recorrente é exigido na entrada da aeronave. Quaisquer valores adicionais
            identificados durante a execução do serviço deverão previamente aprovados e depois serem quitados em até 30 dias ou na entrega da aeronave, o que ocorrer primeiro. Os valores extras são baseados no custo de hora-homem, que é de
                <span lang="pt-BR"><b>R$ 350,00/h</b></span><span lang="pt-BR">, cobrado integralmente na primeira hora e depois a cada 30 minutos. </span><b>(Hora Homem é a soma do Mecânico + Inspetor + RT + CTM + Organização de manutenção).</b>
            </p>
            <ol style="font-weight: bold" start="5">
                <li>
                <b>
                    Formas de Pagamento:
                </b>
                </li>
            </ol>
            <p>
                Nossa política de cobrança para serviços adicionais funciona da seguinte maneira: todas as cobranças de serviços extras são realizadas a cada 30 dias ou na entrega da aeronave, dependendo do que ocorrer primeiro. Isso significa que, se
                sua aeronave receber serviços adicionais, a cobrança desses serviços será feita no intervalo de 30 dias desde a última cobrança ou no momento em que a aeronave for entregue, prevalecendo o que acontecer primeiro.
            </p>
            <p>
            Os pagamentos podem ser realizados à vista ou em até 10 parcelas sem juros no cartão de crédito. Descontos são oferecidos exclusivamente para pagamentos à vista efetuados no momento da retirada da aeronave, ou de pontualidade para
            pagamento em dia do boleto gerado.
            </p>
            <ol style="font-weight: bold" start="6">
                <li>
                Retirada da Aeronave:
                </li>
            </ol>
            <p>
                Após notificarmos o cliente sobre a conclusão do serviço, ele terá até 7 dias para retirar a aeronave. Este prazo será estendido apenas se condições meteorológicas adversas ou outras circunstâncias de força maior impedirem a retirada.
            </p>
            <p>
            <b>Cobrança de Hangaragem:</b> Se a aeronave não for retirada dentro desses 7 dias, será cobrada uma taxa de hangaragem por todo o período, começando na data em que o cliente foi notificado da conclusão do serviço.
            </p>
            <p>
            <b>Liberação da Aeronave:</b> A aeronave só será liberada após o pagamento completo dos serviços e quaisquer taxas de hangaragem acumuladas. Se o pagamento não for efetuado dentro do prazo de retirada, taxas adicionais de hangaragem
                serão acumuladas ao valor devido.
            </p>
            <h4 style="text-align: center">
            TABELAS DE VALORES VIGENTES
            </h4>
            <div class="col-md-12">
                    <table class="table-c" style="width: 100%">
                        <thead>
                            <tr>
                                <th colspan="7" style="text-align: center">
                                    TABLEA DE VALORES CESSNA
                                </th>
                            </tr>
                            <tr>
                                <th colspan="7" style="text-align: center">
                                    TEMPO DE SERVIÇO
                                </th>
                            </tr>
                            <tr>
                                <th>
                                    TIPO DE SERVIÇO
                                </th>
                                <th>
                                    50H
                                </th>
                                <th>
                                    100H
                                </th>
                                <th>
                                    200H
                                </th>
                                <th>
                                    500H
                                </th>
                                <th>
                                    1000H
                                </th>
                                <th>
                                    CVA
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th rowspan="2">
                                    2 LUGARES
                                </th>
                                <td>
                                    8 horas
                                </td>
                                <td>
                                    12 horas
                                </td>
                                <td>
                                    16 horas
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    24 horas
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    R$ 2.200,00
                                </td>
                                <td>
                                    R$ 3.300,00
                                </td>
                                <td>
                                    R$ 4.400,00
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    R$ 6.600,00
                                </td>
                            </tr>
                            <tr>
                                <th rowspan="2">
                                    4 LUGARES
                                </th>
                                <td>
                                    12 horas
                                </td>
                                <td>
                                    16 horas
                                </td>
                                <td>
                                    20 horas
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    28 horas
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    R$ 3.300,00
                                </td>
                                <td>
                                    R$ 4.400,00
                                </td>
                                <td>
                                    R$ 5.500,00
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    R$ 7.700,00
                                </td>
                            </tr>
                            <tr>
                                <th rowspan="2">
                                    6 LUGARES MNTE
                                </th>
                                <td>
                                    16 horas
                                </td>
                                <td>
                                    20 horas
                                </td>
                                <td>
                                    24 horas
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    32 horas
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    R$ 4.400,00
                                </td>
                                <td>
                                    R$ 5.500,00
                                </td>
                                <td>
                                    R$ 6.600,00
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    R$ 8.800,00
                                </td>
                            </tr>
                            <tr>
                                <th rowspan="2">
                                    6 LUGARES MLTE
                                </th>
                                <td>
                                    20 horas
                                </td>
                                <td>
                                    24 horas
                                </td>
                                <td>
                                    28 horas
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    36 horas
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    R$ 5.500,00
                                </td>
                                <td>
                                    R$ 6.600,00
                                </td>
                                <td>
                                    R$ 7.700,00
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    R$ 9.900,00
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <p></p>
                <div class="col-md-12">
                            <table class="table-c" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th colspan="7" style="text-align: center">
                                            TABLEA DE VALORES EMBRAER E PIPER
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" style="text-align: center">
                                            TEMPO DE SERVIÇO
                                        </th>

                                    </tr>
                                    <tr>
                                        <th>
                                            TIPO DE SERVIÇO
                                        </th>
                                        <th>
                                            50H
                                        </th>
                                        <th>
                                            100H
                                        </th>
                                        <th>
                                            200H
                                        </th>
                                        <th>
                                            500H
                                        </th>
                                        <th>
                                            1000H
                                        </th>
                                        <th>
                                            CVA
                                        </th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th rowspan="2">
                                            2 LUGARES
                                        </th>
                                        <td>
                                            8 horas
                                        </td>
                                        <td>
                                            12 horas
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            24 horas
                                        </td>
                                        <td>
                                            24 horas
                                        </td>
                                        <td>
                                            24 horas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            R$ 2.200,00
                                        </td>
                                        <td>
                                            R$ 3.300,00
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            R$ 6.600,00
                                        </td>
                                        <td>
                                            R$ 6.600,00
                                        </td>
                                        <td>
                                            R$ 6.600,00
                                        </td>
                                    </tr>
                                    <tr>
                                        <th rowspan="2">
                                            4 LUGARES
                                        </th>
                                        <td>
                                            12 horas
                                        </td>
                                        <td>
                                            16 horas
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            28 horas
                                        </td>
                                        <td>
                                            28 horas
                                        </td>
                                        <td>
                                            28 horas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            R$ 3.300,00
                                        </td>
                                        <td>
                                            R$ 4.400,00
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            R$ 7.700,00
                                        </td>
                                        <td>
                                            R$ 7.700,00
                                        </td>
                                        <td>
                                            R$ 7.700,00
                                        </td>
                                    </tr>
                                    <tr>
                                        <th rowspan="2">
                                            6 LUGARES MNTE
                                        </th>
                                        <td>
                                            16 horas
                                        </td>
                                        <td>
                                            20 horas
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            32 horas
                                        </td>
                                        <td>
                                            32 horas
                                        </td>
                                        <td>
                                            32 horas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            R$ 4.400,00
                                        </td>
                                        <td>
                                            R$ 5.500,00
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            R$ 8.800,00
                                        </td>
                                        <td>
                                            R$ 8.800,00
                                        </td>
                                        <td>
                                            R$ 8.800,00
                                        </td>
                                    </tr>
                                    <tr>
                                        <th rowspan="2">
                                            6 LUGARES MLTE
                                        </th>
                                        <td>
                                            20 horas
                                        </td>
                                        <td>
                                            24 horas
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            36 horas
                                        </td>
                                        <td>
                                            36 horas
                                        </td>
                                        <td>
                                            36 horas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            R$ 5.500,00
                                        </td>
                                        <td>
                                            R$ 6.600,00
                                        </td>
                                        <td>
                                            &nbsp;
                                        </td>
                                        <td>
                                            R$ 9.900,00
                                        </td>
                                        <td>
                                            R$ 9.900,00
                                        </td>
                                        <td>
                                            R$ 9.900,00
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
            <p></p>
            <p lang="pt-PT" class="western" align="justify" style="margin-right: 0.18cm;">
                <font size="2" style="font-size: 11pt;">
                    <font face="Calibri, serif"><font size="3" style="font-size: 12pt;">*</font></font>
                    <font face="Calibri, serif">
                        <font size="3" style="font-size: 12pt;">
                            Os preços listados na tabela acima são estimativas baseadas nos serviços padrão indicados pelo manual do fabricante e podem variar dependendo da complexidade do serviço requerido e da organização dos documentos da aeronave.
                            Qualquer discrepância, serviço adicional ou serviço contratado de terceiros será avaliado, comunicado ao cliente para aprovação e, se aprovado, cobrado à parte. Esses custos adicionais serão calculados com base na taxa de
                            hora/homem especificada no item 3 deste termo.
                        </font>
                    </font>
                </font>
            </p>
        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="{{url('/')}}/css/select2.css">
    @include('qlib.csslib')
    <link rel="stylesheet" href="{{url('/')}}/DataTables/DataTables-1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="{{url('/')}}/vendor/jquery/jquery.min.js"></script>

@stop
@section('js')
    @include('qlib.jslib')
    <script src="{{url('/')}}/js/select2.min.js" ></script>
    <script src="{{url('/')}}/DataTables/datatables.min.js" ></script>
    <script>
        $('#inp-password').val('');
        $('[mask-cpf]').inputmask('999.999.999-99');
        $('[mask-cnpj]').inputmask('99.999.999/9999-99');
        $('[mask-data]').inputmask('99/99/9999');
        $('[mask-cep]').inputmask('99.999-999');
    </script>
@stop
