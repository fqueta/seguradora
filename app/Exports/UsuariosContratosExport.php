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

    /**
     * Construtor do export mantendo filtros.
     * Constructor storing filters for export.
     */
    /**
     * Construtor que recebe filtros do relatório.
     * Constructor receiving report filters.
     */
    public function __construct(string $campoPeriodo = 'inicio', ?string $inicio = null, ?string $fim = null, ?string $status = null, ?string $search = null)
    {
        $this->campoPeriodo = $campoPeriodo ?: 'inicio';
        $this->inicio = $inicio;
        $this->fim = $fim;
        $this->status = $status;
        $this->search = $search;
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
                DB::raw("cancelmeta.meta_value AS cancelmeta"),
                DB::raw("cancel_ev.cancel_at AS cancel_event_at"),
            ])
            ->join('contratos', 'contratos.id_cliente', '=', 'users.id')
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
            $status = null;
            if (!empty($row->config)) {
                $cfg = is_array($row->config) ? $row->config : (json_decode($row->config, true) ?: []);
                $nascimento = $cfg['dataNascimento'] ?? $cfg['nascimento'] ?? $cfg['data_nasci'] ?? null;
                $status = $cfg['status_contrato'] ?? null;
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