<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function gerarPdf($d=[])
    {
        $dados = $d ? $d : [
            'titulo' => 'Título do PDF',
            'conteudo' => 'Este é o conteúdo do PDF gerado pelo Laravel.',
        ];

        $pdf = Pdf::loadView('qlib.pdf.template_default', $dados);

        // Retornar PDF para download
        return $pdf->download('arquivo.pdf');

        // Retornar PDF no navegador
        // return $pdf->stream('arquivo.pdf');
    }
    /**
     * Metodos gerar e salvar um PDF no disco
     * @param array $conf = ['pasta'=>'','arquivo'=>''];
     * @param array $dados = ['titulo'=>'','conteudo'=>''];
     * @return string
     */
    public function salvarPdf($d=[],$conf=[])
    {
        // Dados para o template
        $dados = $d ? $d : [
            'titulo' => 'Título do PDF',
            'conteudo' => '<p>Programador <b>Fernando Queta</b></p><p>Este é o conteúdo que será salvo no PDF. este é um documento novo mostrando que fncuina html</p>',
        ];

        // Gerar o PDF
        // dd($dados);
        $pdf = Pdf::loadView('qlib.pdf.template_default', $dados);

        // Caminho onde o arquivo será salvo
        $pasta = isset($conf['pasta']) ? $conf['pasta'] : 'pdfs/';
        $arquivo = isset($conf['arquivo']) ? $conf['arquivo'] : 'arquivo.pdf';
        $caminhoArquivo = $pasta.$arquivo;

        // Salvar o PDF no disco local (storage/app/pdfs/arquivo.pdf)
        try {
            Storage::put($caminhoArquivo, $pdf->output());
            return [
                'exec' => true,
                'color' => 'success',
                'mens' => 'PDF salvo com sucesso!',
                'caminho' => Storage::url($caminhoArquivo),
            ];
        } catch (\Throwable $e) {
            $ret['exec'] = false;
            $ret['mens'] = 'Erro ao salvar';
            $ret['color'] = 'danger';
            $ret['error'] = $e->getMessage();
            //throw $th;
            return $ret;
        }
    }
}
