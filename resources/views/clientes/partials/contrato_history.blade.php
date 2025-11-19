{{--
    Partial: Timeline de eventos do contrato para uso em painel_sulamerica.

    Português: Este partial consulta o contrato pelo token (se necessário)
    e lista os eventos relacionados para montagem de uma timeline dentro
    da página de detalhes do cliente/contrato.

    English: Partial view that fetches contract by token (if needed) and
    lists related events to render a timeline inside the client panel.

    Parâmetros esperados:
    - token: string (obrigatório se não for fornecido $contrato/$events)
    - contrato: \App\Models\Contrato (opcional)
    - events: Illuminate\Support\Collection de \App\Models\ContractEvent (opcional)
--}}

@php
    // Resolve contrato e eventos se não vierem por parâmetro
    /**
     * Garante que $contrato e $events estejam carregados.
     * Se apenas $token foi passado, faz a consulta usando Eloquent.
     */
    if (!isset($contrato) || !$contrato) {
        $contrato = isset($token) ? \App\Models\Contrato::where('token', $token)->first() : null;
    }
    if (!isset($events) || !$events) {
        if ($contrato) {
            // Eventos de integração SulAmérica, reativação e cancelamento
            $events = \App\Models\ContractEvent::where('contrato_id', $contrato->id)
                ->whereIn('event_type', ['status_update','reativacao','cancelamento_end'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $events = collect();
        }
    } else {
        // Se eventos vierem externos, filtra para mostrar apenas tipos exibíveis
        if ($events instanceof \Illuminate\Support\Collection) {
            $events = $events->filter(function($e){
                return in_array($e->event_type, ['status_update','reativacao','cancelamento_end']);
            });
        }
    }
@endphp

<div class="card mt-3">
    <div class="card-body">
        <h4 class="mb-3"><i class="fas fa-history mr-1"></i> {{ __('Histórico do Contrato') }}</h4>
        @if(!$contrato)
            <div class="alert alert-warning">{{ __('Contrato não encontrado para o token informado.') }}</div>
        @else
            <p class="text-muted">
                {{ __('Contrato') }}: <strong>{{ $contrato->token }}</strong>
                @if(isset($contrato->status_contrato))
                    &mdash; {{ __('Status') }}: <span class="badge badge-info">{{ $contrato->status_contrato }}</span>
                @endif
            </p>

            @if($events->isEmpty())
                <div class="alert alert-secondary">{{ __('Nenhum evento registrado para este contrato.') }}</div>
            @else
                <div class="timeline">
                    @foreach($events as $event)
                        <div class="timeline-item">
                            <div class="timeline-time">{{ $event->created_at->format('d/m/Y H:i') }}</div>
                            <div class="timeline-title">
                                @if($event->event_type === 'integracao_sulamerica')
                                    <span class="badge badge-primary">Integração SulAmérica</span>
                                @elseif($event->event_type === 'reativacao')
                                    <span class="badge badge-success">Reativação</span>
                                @elseif($event->event_type === 'cancelamento')
                                    <span class="badge badge-warning">Cancelamento</span>
                                @else
                                    <span class="badge badge-secondary">{{ $event->event_type }}</span>
                                @endif
                                @if($event->from_status || $event->to_status)
                                    &mdash; {{ __('Status') }}: {{ $event->from_status ?? 'N/A' }} → {{ $event->to_status ?? 'N/A' }}
                                @endif
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <p>{{ $event->description }}</p>
                                    @if($event->user)
                                        <p>{{ __('Usuário') }}: <strong>{{ $event->user->name }}</strong></p>
                                    @elseif($event->user_id)
                                        <p>{{ __('Usuário ID') }}: <strong>{{ $event->user_id }}</strong></p>
                                    @endif
                                    @php
                                        /**
                                         * Exibe metadados do evento com suporte a retornoMsg.
                                         * Português: Converte metadata para array e tenta extrair 'retornoMsg'.
                                         * English: Convert metadata to array and try to extract 'retornoMsg'.
                                         */
                                        $metaRaw = $event->metadata;
                                        $metaArr = null;
                                        if (is_string($metaRaw)) {
                                            $decoded = json_decode($metaRaw, true);
                                            $metaArr = is_array($decoded) ? $decoded : null;
                                        } elseif (is_array($metaRaw)) {
                                            $metaArr = $metaRaw;
                                        }
                                        // Closure para localizar 'retornoMsg' em estruturas comuns ou por busca recursiva
                                        $getRetornoMsg = function($meta) {
                                            if (!is_array($meta)) return null;
                                            $paths = [
                                                ['response','data','retornoMsg'],
                                                ['ret','data','retornoMsg'],
                                                ['data','retornoMsg'],
                                                ['retornoMsg'],
                                            ];
                                            foreach ($paths as $path) {
                                                $cur = $meta;
                                                $ok = true;
                                                foreach ($path as $seg) {
                                                    if (is_array($cur) && array_key_exists($seg, $cur)) {
                                                        $cur = $cur[$seg];
                                                    } else {
                                                        $ok = false; break;
                                                    }
                                                }
                                                if ($ok && (is_string($cur) || is_numeric($cur))) {
                                                    return (string)$cur;
                                                }
                                            }
                                            // Busca recursiva como fallback
                                            $stack = [$meta];
                                            while ($stack) {
                                                $item = array_pop($stack);
                                                foreach ($item as $k => $v) {
                                                    if ($k === 'retornoMsg' && (is_string($v) || is_numeric($v))) {
                                                        return (string)$v;
                                                    }
                                                    if (is_array($v)) { $stack[] = $v; }
                                                }
                                            }
                                            return null;
                                        };
                                        $retornoMsg = !is_null($metaArr) ? $getRetornoMsg($metaArr) : null;
                                        // PT/EN: Extração de 'mens' e 'parametros' em caminhos comuns
                                        $getNested = function($meta, $path) {
                                            if (!is_array($meta)) return null;
                                            $cur = $meta;
                                            foreach ($path as $seg) {
                                                if (is_array($cur) && array_key_exists($seg, $cur)) { $cur = $cur[$seg]; }
                                                else { return null; }
                                            }
                                            return $cur;
                                        };
                                        $mens = null;
                                        foreach ([['response','mens'], ['data','mens'], ['mens']] as $p) {
                                            $val = $getNested($metaArr ?? [], $p);
                                            if (is_string($val) || is_numeric($val)) { $mens = (string)$val; break; }
                                        }
                                        if (is_null($mens) && is_array($metaArr)) {
                                            // Fallback recursivo para localizar 'mens'
                                            $stack = [$metaArr];
                                            while ($stack && is_null($mens)) {
                                                $item = array_pop($stack);
                                                foreach ($item as $k => $v) {
                                                    if ($k === 'mens' && (is_string($v) || is_numeric($v))) { $mens = (string)$v; break; }
                                                    if (is_array($v)) { $stack[] = $v; }
                                                }
                                            }
                                        }
                                        $parametros = null;
                                        foreach ([['response','data','parametros'], ['data','parametros'], ['ret','data','parametros'], ['parametros']] as $p) {
                                            $val = $getNested($metaArr ?? [], $p);
                                            if (is_array($val)) { $parametros = $val; break; }
                                        }
                                    @endphp
                                    @if(!is_null($metaArr))
                                        @if(!empty($mens))
                                            <p>{{ __('Mensagem') }}: <strong>{{ $mens }}</strong></p>
                                        @elseif(!empty($retornoMsg))
                                            <p>{{ __('Mensagem') }}: <strong>{{ $retornoMsg }}</strong></p>
                                        @endif
                                        @if(is_array($parametros) && !empty($parametros))
                                            {{-- PT: Tabela de parâmetros quando presentes em response.data.parametros --}}
                                            {{-- EN: Parameters table when present in response.data.parametros --}}
                                            <div class="table-responsive mb-2">
                                                <table class="table table-sm table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 35%">{{ __('Campo') }}</th>
                                                            <th>{{ __('Valor') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($parametros as $pk => $pv)
                                                            <tr>
                                                                <td>{{ $pk }}</td>
                                                                <td>
                                                                    @if(is_array($pv))
                                                                        <code>{{ json_encode($pv, JSON_UNESCAPED_UNICODE) }}</code>
                                                                    @else
                                                                        {{ is_bool($pv) ? ($pv ? 'true' : 'false') : (string)$pv }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                        {{-- dados json server ser exibido apenas para o usuario desenvolvedor id_permissao= 1 --}}
                                        @if(auth()->user()->id_permissao == 1)
                                        <div class="meta">
                                            {{-- PT: Exibir JSON (array) de metadados dentro de acordeão colapsável --}}
                                            {{-- EN: Show array metadata JSON inside collapsible accordion --}}
                                            @php
                                                $arrCollapseId = 'meta-json-'.($event->id ?? $loop->index);
                                            @endphp
                                            <p class="mb-2">
                                                <button class="btn btn-xs btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $arrCollapseId }}" aria-expanded="false" aria-controls="{{ $arrCollapseId }}">
                                                    {{ __('Ver JSON') }}
                                                </button>
                                            </p>
                                            <div id="{{ $arrCollapseId }}" class="collapse">
                                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($metaArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                        @endif
                                    @elseif(!empty($metaRaw))
                                        {{-- dados json server ser exibido apenas para o usuario desenvolvedor id_permissao= 1 --}}
                                        @if(auth()->user()->id_permissao == 1)
                                        <div class="meta">
                                            {{-- PT: Exibir JSON de metadados dentro de um acordeão colapsável (visível sob demanda) --}}
                                            {{-- EN: Show metadata JSON inside a collapsible accordion (visible on demand) --}}
                                            @php
                                                // PT: Construir um ID único por evento para o alvo do acordeão
                                                // EN: Build a unique per-event ID for the accordion target
                                                $rawCollapseId = 'meta-raw-'.($event->id ?? $loop->index);
                                            @endphp
                                            <p class="mb-2">
                                                <button class="btn btn-xs btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $rawCollapseId }}" aria-expanded="false" aria-controls="{{ $rawCollapseId }}">
                                                    {{ __('Ver JSON') }}
                                                </button>
                                            </p>
                                            <div id="{{ $rawCollapseId }}" class="collapse">
                                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ is_scalar($metaRaw) ? (string) $metaRaw : json_encode($metaRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

<style>
/* Estilos básicos para linha do tempo dentro do painel */
.timeline { position: relative; padding: 10px 0; }
.timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #e0e0e0; }
.timeline-item { position: relative; margin-left: 40px; margin-bottom: 12px; }
.timeline-item::before { content: ''; position: absolute; left: -25px; top: 5px; width: 10px; height: 10px; background: #3c8dbc; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #3c8dbc; }
.timeline-time { color: #888; font-size: 0.85rem; }
.timeline-title { font-weight: 600; margin-bottom: 4px; }
.meta { font-size: 0.9rem; background: #f9f9f9; padding: 8px; border-radius: 4px; }

/* PT: Estilos de impressão para a timeline (layout limpo, sem botões e sem JSON) */
/* EN: Print styles for the timeline (clean layout, hide buttons and raw JSON) */
@media print {
  /* Remover elementos interativos e decoração visual pesada */
  .btn, .no-print { display: none !important; }
  .timeline::before, .timeline-item::before { display: none !important; }

  /* Simplificar margens para melhor aproveitamento de página */
  .timeline { padding: 0; }
  .timeline-item { margin-left: 0; page-break-inside: avoid; }
  .card { box-shadow: none; border: 1px solid #ddd; }
  .meta { background: #fff; }

  /* Tamanhos de fonte legíveis em papel */
  .timeline-time { color: #000; font-size: 10pt; }
  .timeline-title { font-size: 12pt; }
  .table { font-size: 10pt; }
  pre { font-size: 9pt; }

  /* Ocultar conteúdo colapsável (JSON bruto) para impressão enxuta */
  .collapse { display: none !important; }
}
</style>
