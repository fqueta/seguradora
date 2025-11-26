<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class UsuariosContratosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected string $campoPeriodo;
    protected ?string $inicio;
    protected ?string $fim;
    protected ?string $status;
    protected ?string $search;
    protected ?string $autor;

    /**
     * Construtor do export mantendo filtros.
     * Constructor storing filters for export.
     */
    /**
     * Construtor que recebe filtros do relatório.
     * Constructor receiving report filters.
     */
    /**
     * @param string $campoPeriodo Campo base para filtro de período (inicio|fim)
     * @param ?string $inicio Data inicial (YYYY-MM-DD)
     * @param ?string $fim Data final (YYYY-MM-DD)
     * @param ?string $status Status do contrato
     * @param ?string $search Busca livre (nome/CPF)
     * @param ?string $autor ID do autor do contrato (users.id)
     */
    public function __construct(string $campoPeriodo = 'inicio', ?string $inicio = null, ?string $fim = null, ?string $status = null, ?string $search = null, ?string $autor = null)
    {
        $this->campoPeriodo = $campoPeriodo ?: 'inicio';
        $this->inicio = $inicio;
        $this->fim = $fim;
        $this->status = $status;
        $this->search = $search;
        $this->autor = $autor;
    }

    /**
     * Retorna a coleção de linhas para o Excel.
     * Returns a collection of rows for Excel.
     */
    public function collection()
    {
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
                // Inclui status atual via usermeta para manter consistência com filtros
                DB::raw("statusmeta.meta_value AS status_meta"),
            ])
            ->join('contratos', 'contratos.id_cliente', '=', 'users.id')
            // Restringe aos clientes (id_permission > 4)
            ->where('users.id_permission', '>', 4)
            // Traz o nome do autor
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
            });

        if ($this->inicio && $this->fim) {
            if ($this->campoPeriodo === 'fim') {
                $query->whereBetween('contratos.fim', [$this->inicio, $this->fim]);
            } else {
                $query->whereBetween('contratos.inicio', [$this->inicio, $this->fim]);
            }
        } elseif ($this->inicio) {
            if ($this->campoPeriodo === 'fim') {
                $query->where('contratos.fim', '>=', $this->inicio);
            } else {
                $query->where('contratos.inicio', '>=', $this->inicio);
            }
        } elseif ($this->fim) {
            if ($this->campoPeriodo === 'fim') {
                $query->where('contratos.fim', '<=', $this->fim);
            } else {
                $query->where('contratos.inicio', '<=', $this->fim);
            }
        }

        if (!empty($this->status)) {
            $query->where('statusmeta.meta_value', '=', $this->status);
        }

        // Filtro por autor do contrato
        if (!empty($this->autor)) {
            $query->where('contratos.autor', '=', $this->autor);
        }

        // Busca livre: nome ou CPF
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', "%{$this->search}%")
                  ->orWhere('users.cpf', 'like', "%{$this->search}%");
            });
        }

        $dados = $query->orderBy('users.name', 'asc')->get();

        return $dados->map(function ($row) {
            $nascimento = null;
            // Prioriza status do meta (usermeta); se ausente, usa o config
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
                'Data de inicio' => $this->formatDateBr($row->contrato_inicio),
                'Data de Fim' => $this->formatDateBr($row->contrato_fim),
                'Data de cancelamento' => $this->formatDateBr($cancelamento),
                'Nome' => $row->name,
                'Autor' => $row->autor_name,
                'CPF' => $row->cpf,
                'Data de Nascimento' => $this->formatDateBr($nascimento),
                'Status' => $status,
            ];
        });
    }

    /**
     * Cabeçalhos do Excel.
     * Excel column headings.
     */
    public function headings(): array
    {
        return [
            'Data de inicio',
            'Data de Fim',
            'Data de cancelamento',
            'Nome',
            'Autor',
            'CPF',
            'Data de Nascimento',
            'Status',
        ];
    }

    /**
     * Formata uma data para o padrão brasileiro DD/MM/YYYY.
     * Format a date string to Brazilian format DD/MM/YYYY.
     */
    private function formatDateBr($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            if (is_string($date)) {
                foreach (['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y'] as $fmt) {
                    try {
                        $dt = Carbon::createFromFormat($fmt, $date);
                        return $dt ? $dt->format('d/m/Y') : $date;
                    } catch (\Throwable $th) {
                        // try next format
                    }
                }
                return Carbon::parse($date)->format('d/m/Y');
            }
        } catch (\Throwable $th) {
            return is_string($date) ? $date : null;
        }
        return null;
    }
}