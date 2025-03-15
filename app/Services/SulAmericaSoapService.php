<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Hash;

class SulAmericaSoapService
{
    protected $url;

    public function __construct()
    {
        // $this->url = "https://services.sulamerica.com.br/saude/autorizacao"; // URL do WebService da SulAmérica
        $this->url = "https://canalvenda-internet-develop.executivoslab.com.br/services/canalvenda?wsdl"; // URL do WebService da SulAmérica
    }

    public function call()
    {
        // Criar o XML da requisição SOAP
        $user = 'yello1232user';
        $pass = md5('yello1232pass');
        $xml = '
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:br.com.sulamerica.canalvenda.ws">
            <soapenv:Header>
                <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss wssecuritysecext-1.0.xsd">
                    <wsse:UsernameToken>
                        <wsse:Username>'.$user.'</wsse:Username>
                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username tokenprofile-1.0#PasswordText">
                            '.$pass.'
                        </wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <urn:contratarSeguro>
                    <urn:produto>551</urn:produto>
                    <urn:canalVenda>SITE</urn:canalVenda>
                    <urn:operacaoParceiro>Oper001</urn:operacaoParceiro>
                    <urn:parametros>
                        <![CDATA[ <parametros> <idade>20</idade> <sexo>F</sexo> <uf>RJ</uf> <nomeSegurado>Jose da Silva</nomeSegurado> <email>josesilva@email.com</email> <tipoDocumento>C</tipoDocumento> <documento>22812465498</documento>
                        <inicioVigencia>2010-03-01</inicioVigencia> <fimVigencia>2010-03-05</fimVigencia> <dataNascimento>1986-01-02</dataNascimento> <tipoAssistencia>N</tipoAssistencia> </parametros> ]]>
                    </urn:parametros>
                </urn:contratarSeguro>
            </soapenv:Body>
        </soapenv:Envelope>';

        // Fazer a requisição HTTP
        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            // 'SOAPAction' => 'http://sua.url.namespace/' . $method,
        ])->withBody($xml, 'text/xml')->post($this->url);

        return $response->body(); // Retorna a resposta do WebService
    }

    // public function autorizacao($numeroGuia, $cpfBeneficiario)
    public function autorizacao()
    {
        // return $this->call('autorizacao', [
        //     'numeroGuia' => $numeroGuia,
        //     'cpfBeneficiario' => $cpfBeneficiario
        // ]);
        return $this->call('autorizacao');
    }
    // public function consultarGuia($numeroGuia, $cpfBeneficiario)
    // {
    //     return $this->call('ConsultarGuia', [
    //         'numeroGuia' => $numeroGuia,
    //         'cpfBeneficiario' => $cpfBeneficiario
    //     ]);
    // }
}
