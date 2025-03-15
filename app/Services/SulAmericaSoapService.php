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
        // $pass = md5('yello1232pass');
        $pass = 'yello1232pass';
        $Security = '
                <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss wssecuritysecext-1.0.xsd">
                    <wsse:UsernameToken>
                        <wsse:Username>'.$user.'</wsse:Username>
                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username tokenprofile-1.0#PasswordText">
                            '.$pass.'
                        </wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
        ';
        $Security = '
                <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/07/secext" soapenv:mustUnderstand="1">
                    <wsse:UsernameToken>
                        <wsse:Username>'.$user.'</wsse:Username>
                        <wsse:Password>'.$pass.'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
        ';
        $xml = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:br.com.sulamerica.canalvenda.ws">
            <soapenv:Header>
                '.$Security.'
            </soapenv:Header>
            <soapenv:Body>
                <urn:contratarSeguro>
                    <urn:produto>99999</urn:produto>
                    <urn:canalVenda>SITE</urn:canalVenda>
                    <urn:operacaoParceiro>9999999999</urn:operacaoParceiro>
                    <urn:parametros>
                        <![CDATA[ <parametros> <planoProduto>1</planoProduto> <premioSeguro>1.00</premioSeguro> <nomeSegurado>Pessoa de Teste</nomeSegurado> <dataNascimento>1961-07-26</dataNascimento> <sexo>M</sexo> <uf>PI</uf>
                        <tipoDocumento>C</tipoDocumento> <documento>12345678901</documento> <inicioVigencia>2024-05-16</inicioVigencia> <fimVigencia>2025-05-16</fimVigencia> </parametros>]]>
                    </urn:parametros>
                </urn:contratarSeguro>
            </soapenv:Body>
        </soapenv:Envelope>
        ';

        // Fazer a requisição HTTP
        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            // 'SOAPAction' => 'http://sua.url.namespace/' . $method,
        ])->withBody($xml, 'text/xml')->post($this->url);

        $ret['url'] = $this->url;
        $ret['requsição'] = $xml;
        $ret['body'] = $response->body();
        return $ret; // Retorna a resposta do WebService
    }
    // public function call()
    // {
    //     // Criar o XML da requisição SOAP
    //     $user = 'yello1232user';
    //     $pass = 'yello1232pass';
    //     $pass = md5($pass);
    //     $Security = '
    //             <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss wssecuritysecext-1.0.xsd">
    //                 <wsse:UsernameToken>
    //                     <wsse:Username>'.$user.'</wsse:Username>
    //                     <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username tokenprofile-1.0#PasswordText">
    //                         '.$pass.'
    //                     </wsse:Password>
    //                 </wsse:UsernameToken>
    //             </wsse:Security>
    //     ';
    //     $Security = '
    //             <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/07/secext" soapenv:mustUnderstand="1">
    //                 <wsse:UsernameToken>
    //                     <wsse:Username>'.$user.'</wsse:Username>
    //                     <wsse:Password>'.$pass.'</wsse:Password>
    //                 </wsse:UsernameToken>
    //             </wsse:Security>
    //     ';
    //     $xml = '
    //     <soapenv:Envelope
    //         xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    //         xmlns:urn="urn:br.com.sulamerica.canalvenda.ws"
    //         xmlns:NS1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss
    //     wssecurity-secext-1.0.xsd"
    //     >
    //         <soapenv:Header>
    //             <NS1:Security soapenv:mustUnderstand="1">
    //                 <NS1:UsernameToken>
    //                     <NS1:Username>'.$user.'</NS1:Username>
    //                     <NS1:Password>'.$pass.'</NS1:Password>
    //                 </NS1:UsernameToken>
    //             </NS1:Security>
    //         </soapenv:Header>
    //         <soapenv:Body>
    //             <urn:contratarSeguro>
    //                 <urn:produto>99999</urn:produto>
    //                 <urn:canalVenda>SITE</urn:canalVenda>
    //                 <urn:operacaoParceiro>9999999999</urn:operacaoParceiro>
    //                 <urn:parametros>
    //                     <![CDATA[ <parametros> <planoProduto>1</planoProduto> <premioSeguro>1.00</premioSeguro> <nomeSegurado>Pessoa de Teste</nomeSegurado> <dataNascimento>1961-07-26</dataNascimento> <sexo>M</sexo> <uf>PI</uf>
    //                     <tipoDocumento>C</tipoDocumento> <documento>12345678901</documento> <inicioVigencia>2024-05-16</inicioVigencia> <fimVigencia>2025-05-16</fimVigencia> </parametros>]]>
    //                 </urn:parametros>
    //             </urn:contratarSeguro>
    //         </soapenv:Body>
    //     </soapenv:Envelope>

    //     ';

    //     // Fazer a requisição HTTP
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'text/xml; charset=utf-8',
    //         'SOAPAction' => 'http://sua.url.namespace/' . $method,
    //     ])->withBody($xml, 'text/xml')->post($this->url);
    //     $ret['url'] = $this->url;
    //     $ret['requsição'] = $xml;
    //     $ret['body'] = $response->body();
    //     return $ret; // Retorna a resposta do WebService
    // }

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
