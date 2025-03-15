<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SulAmericaSoapService;

class SulAmericaController extends Controller
{
    protected $soapService;

    public function __construct(SulAmericaSoapService $soapService)
    {
        $this->soapService = $soapService;
    }

    public function solicitarAutorizacao(Request $request)
    {
        // $numeroGuia = $request->input('numeroGuia');
        // $cpf = $request->input('cpf');

        $resultado = $this->soapService->autorizacao();

        return response()->json(['response' => $resultado]);
    }
    public function consultarGuia(Request $request)
    {
        $numeroGuia = $request->input('numeroGuia');
        $cpf = $request->input('cpf');

        $resultado = $this->soapService->consultarGuia($numeroGuia, $cpf);

        return response()->json(['response' => $resultado]);
    }
}
