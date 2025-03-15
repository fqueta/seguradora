<?php
try {
    $wsdl = "https://canalvenda-internet-develop.executivoslab.com.br/services/canalvenda?wsdl"; // Coloque a URL correta do WSDL
    $client = new SoapClient($wsdl, ['trace' => true, 'exceptions' => true]);

    // Chamando um método do WebService (exemplo fictício)
    $params = [
        'numeroGuia' => '123456',
        'cpfBeneficiario' => '99999999999'
    ];
    $response = $client->__soapCall('nomeDoMetodo', [$params]);

    echo "Resposta: ";
    print_r($response);
} catch (Exception $e) {
    echo "Erro na requisição: " . $e->getMessage();
}
