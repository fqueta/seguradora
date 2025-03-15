<?php

$wsdl = "https://canalvenda-internet-develop.executivoslab.com.br/services/canalvenda?wsdl"; // Coloque a URL correta do WSDL

try {
    $client = new SoapClient($wsdl, [
        'trace' => true,
        'exceptions' => true
    ]);
    echo "SoapClient funcionando com WSDL!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
