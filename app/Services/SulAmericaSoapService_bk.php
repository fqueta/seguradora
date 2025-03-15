<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SulAmericaSoapService
{
    protected $url;

    public function __construct()
    {
        $this->url = "https://services.sulamerica.com.br/saude/autorizacao"; // URL do WebService da SulAmérica
    }

    public function call($method, $params)
    {
        // Criar o XML da requisição SOAP
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://sua.url.namespace/">
            <soapenv:Header/>
            <soapenv:Body>
                <web:' . $method . '>
                    <web:numeroGuia>' . $params['numeroGuia'] . '</web:numeroGuia>
                    <web:cpfBeneficiario>' . $params['cpfBeneficiario'] . '</web:cpfBeneficiario>
                </web:' . $method . '>
            </soapenv:Body>
        </soapenv:Envelope>';

        // Fazer a requisição HTTP
        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://sua.url.namespace/' . $method,
        ])->withBody($xml, 'text/xml')->post($this->url);

        return $response->body(); // Retorna a resposta do WebService
    }

    public function consultarGuia($numeroGuia, $cpfBeneficiario)
    {
        return $this->call('ConsultarGuia', [
            'numeroGuia' => $numeroGuia,
            'cpfBeneficiario' => $cpfBeneficiario
        ]);
    }
}
