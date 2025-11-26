<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\UsuariosContratosExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class RelatoriosContratosController extends Controller
{
    /**
     * Exibe a página de relatório de usuários com contratos, com filtro por período.
     * Show the user contracts report page with period filtering.
     *
     * Params (GET):
     * - periodo_inicio: Data inicial (YYYY-MM-DD)
     * - periodo_fim: Data final (YYYY-MM-DD)
     * - campo_periodo: Campo base para o período (inicio|fim). Default: inicio
     */
    public function index(Request $request)
    {
        $campoPeriodo = $request->get('campo_periodo', 'inicio');
        $inicio = $request->get('periodo_inicio');
        $fim = $request->get('periodo_fim');
        $status = $request->get('status');
        $autor = $request->get('autor');
        // Captura busca livre do topo (AdminLTE) ou parâmetro genérico
        $search = $request->get('adminlteSearch', $request->get('search'));

        // Opções de status a partir das tags configuradas
        $statusOptions = DB::table('tags')
            ->where('ativo', 's')
            ->where('pai', 'status_contratos')
            ->orderBy('nome', 'asc')
            ->pluck('nome', 'value');

        // Base query: users com join em contratos; join leve em usermeta para leitura da data de cancelamento
        $query = User::select([
                'users.id',
                'users.name',
                'users.cpf',
                'users.config',
                'contratos.inicio as contrato_inicio',
                'contratos.fim as contrato_fim',
                DB::raw('autor.name AS autor_name'),
                DB::raw("cancelmeta.meta_value AS cancelmeta"),
                DB::raw("cancel_ev.cancel_at AS cancel_event_at"),
                // Status atual (usermeta) para alinhar exibição e filtro
                DB::raw("statusmeta.meta_value AS status_meta"),
            ])
            ->join('contratos', 'contratos.id_cliente', '=', 'users.id')
            // Traz o nome do autor do contrato
            ->leftJoin('users as autor', 'autor.id', '=', 'contratos.autor')
            // Join em subconsulta: última ocorrência de cancelamento por evento de status
            ->leftJoin(DB::raw('(
                SELECT contrato_id, MAX(created_at) AS cancel_at
                FROM contract_events
                WHERE event_type = "status_update" AND to_status = "Cancelado"
                GROUP BY contrato_id
            ) as cancel_ev'), function($join){
                $join->on('cancel_ev.contrato_id', '=', 'contratos.id');
            })
            ->leftJoin('usermeta as cancelmeta', function ($join) {
                $join->on('cancelmeta.user_id', '=', 'users.id')
                     ->where('cancelmeta.meta_key', '=', 'status_req_cancelado');
            })
            ->leftJoin('usermeta as statusmeta', function ($join) {
                $join->on('statusmeta.user_id', '=', 'users.id')
                     ->where('statusmeta.meta_key', '=', 'status_contrato');
            })
            // Restringe aos clientes (id_permission > 4)
            // Nota: no sistema, clientes possuem permissão >= 5
            ->where('users.id_permission', '>', 4);

        // Aplica filtro de período conforme campo escolhido
        if ($inicio && $fim) {
            if ($campoPeriodo === 'fim') {
                $query->whereBetween('contratos.fim', [$inicio, $fim]);
            } else {
                $query->whereBetween('contratos.inicio', [$inicio, $fim]);
            }
        } elseif ($inicio) {
            if ($campoPeriodo === 'fim') {
                $query->where('contratos.fim', '>=', $inicio);
            } else {
                $query->where('contratos.inicio', '>=', $inicio);
            }
        } elseif ($fim) {
            if ($campoPeriodo === 'fim') {
                $query->where('contratos.fim', '<=', $fim);
            } else {
                $query->where('contratos.inicio', '<=', $fim);
            }
        }

        // Filtro por status de contrato, quando selecionado
        if (!empty($status)) {
            $query->where('statusmeta.meta_value', '=', $status);
        }
        // Filtro por autor do contrato, quando selecionado
        if (!empty($autor)) {
            $query->where('contratos.autor', '=', $autor);
        }

        // Filtro de busca livre: nome ou CPF
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%$search%")
                  ->orWhere('users.cpf', 'like', "%$search%");
            });
        }

        $registros = $query->orderBy('users.name', 'asc')->paginate(25)->appends($request->query());

        // Mapeia dados para a tabela: resolve nascimento e cancelamento
        $rows = $registros->getCollection()->map(function ($row) {
            // Nascimento pode estar em users.config (JSON) com chaves diferentes
            $nascimento = null;
            // Prioriza status do meta (usermeta) e, se ausente, usa config
            $status = $row->status_meta ?? null;
            if (!empty($row->config)) {
                $cfg = is_array($row->config) ? $row->config : (json_decode($row->config, true) ?: []);
                $nascimento = $cfg['dataNascimento'] ?? $cfg['nascimento'] ?? $cfg['data_nasci'] ?? null;
                if ($status === null) {
                    $status = $cfg['status_contrato'] ?? null;
                }
            }

            // Data de cancelamento: preferir última ocorrência em contract_events
            $cancelamento = $row->cancel_event_at ?? null;
            if (!$cancelamento && !empty($row->cancelmeta)) {
                $meta = json_decode($row->cancelmeta, true);
                if (is_array($meta) && isset($meta['data_cancelamento'])) {
                    $cancelamento = $meta['data_cancelamento'];
                }
            }

            return [
                'id' => $row->id,
                'contrato_inicio' => $this->formatDateBr($row->contrato_inicio),
                'contrato_fim' => $this->formatDateBr($row->contrato_fim),
                'data_cancelamento' => $this->formatDateBr($cancelamento),
                'nome' => $row->name,
                'autor' => $row->autor_name,
                'cpf' => $row->cpf,
                'nascimento' => $this->formatDateBr($nascimento),
                'status' => $status,
            ];
        });

        // Substitui a collection mapeada para exibição, preservando a paginação
        $registros->setCollection(collect($rows));

        /**
         * Opções de autores (contratos.autor) para o select, incluindo contagem de contratos
         * apenas para clientes (users.id_permission > 4). Isso ajuda o usuário a escolher
         * autores mais relevantes.
         */
        $authorsOptions = DB::table('users as u')
            ->join('contratos as c', 'c.autor', '=', 'u.id')
            ->join('users as cli', 'c.id_cliente', '=', 'cli.id')
            ->where('cli.id_permission', '>', 4)
            ->select('u.id', 'u.name', DB::raw('COUNT(c.id) as total'))
            ->groupBy('u.id', 'u.name')
            ->orderBy('u.name', 'asc')
            ->get()
            ->mapWithKeys(function($row){
                return [ $row->id => $row->name . ' (' . $row->total . ')' ];
            });

        return view('admin.relatorios.usuarios_contratos', [
            'registros' => $registros,
            'filtros' => [
                'campo_periodo' => $campoPeriodo,
                'periodo_inicio' => $inicio,
                'periodo_fim' => $fim,
                'status' => $status,
                'search' => $search,
                'autor' => $autor,
            ],
            'statusOptions' => $statusOptions,
            'authorsOptions' => $authorsOptions,
        ]);
    }

    /**
     * Exporta o relatório para Excel mantendo os filtros.
     * Export the filtered report to Excel.
     */
    public function export(Request $request)
    {
        $campoPeriodo = $request->get('campo_periodo', 'inicio');
        $inicio = $request->get('periodo_inicio');
        $fim = $request->get('periodo_fim');
        $status = $request->get('status');
        $search = $request->get('adminlteSearch', $request->get('search'));
        $autor = $request->get('autor');

        // Propaga o filtro de autor e mantém restrição de permissões no export
        $export = new UsuariosContratosExport($campoPeriodo, $inicio, $fim, $status, $search, $autor);
        $nome = 'usuarios_contratos_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $nome);
    }

    /**
     * Formata uma data para o padrão brasileiro DD/MM/YYYY.
     * Format a date string to Brazilian format DD/MM/YYYY.
     *
     * Accepts common formats like `Y-m-d`, `Y-m-d H:i:s`, or already formatted values.
     */
    private function formatDateBr($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            if (is_string($date)) {
                // Tenta formatos específicos primeiro
                foreach (['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y'] as $fmt) {
                    try {
                        $dt = Carbon::createFromFormat($fmt, $date);
                        return $dt ? $dt->format('d/m/Y') : $date;
                    } catch (\Throwable $th) {
                        // continua tentando
                    }
                }
                // Fallback: parse genérico
                return Carbon::parse($date)->format('d/m/Y');
            }
        } catch (\Throwable $th) {
            return is_string($date) ? $date : null;
        }
        return null;
    }
}