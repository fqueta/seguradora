{{--
    Exibe a timeline de eventos do contrato.

    Português: View para listar o histórico de eventos de um contrato,
    permitindo montar uma timeline na tela de detalhes.

    English: Blade view to display the contract events history, useful for
    rendering a timeline in the contract details page.
--}}
<div class="card card-primary card-outline mb-5">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-1"></i> {{ __('Histórico do Contrato') }}</h3>
        <div class="card-tools d-print-none">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @php
            // Exibe apenas eventos de integração SulAmérica, reativação e cancelamento
            // dd($events);
            if (isset($events) && $events instanceof \Illuminate\Support\Collection) {
                $events1 = $events->filter(function($e){
                    return in_array($e->event_type, ['status_update']);
                });
                $events2 = $events->filter(function($e){
                    return in_array($e->event_type, ['integracao_sulamerica','reativacao','cancelamento','cancelamento_end']);
                });
            }
        @endphp
        <p>
            {{ __('Contrato') }}: <strong>{{ $contrato->token }}</strong> {{ __('ID') }}: <b>{{ $contrato->id }}</b>
            @if(isset($contrato->status_contrato))
                &mdash; {{ __('Status') }}: <span class="badge badge-info">{{ $contrato->status_contrato }}</span>
            @endif
        </p>
        <div class="row">
            <div class="col-md-6">
                <h4>{{ __('Eventos de Status') }}</h4>
                @if($events1->isEmpty())
                    <div class="alert alert-secondary">{{ __('Nenhum evento registrado para este contrato.') }}</div>
                @else
                    <div class="timeline">
                        @foreach($events1 as $event)
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
                                        @endphp
                                        @if(!is_null($metaArr))
                                            <div class="meta">
                                                @if(!empty($retornoMsg))
                                                    <p>{{ __('Mensagem') }}: <strong>{{ $retornoMsg }}</strong></p>
                                                @endif
                                                {{-- PT: JSON (array) em acordeão colapsável para visualização sob demanda --}}
                                                {{-- EN: Array JSON in collapsible accordion for on-demand viewing --}}
                                                @php
                                                    $arrCollapseId = 'admin-meta-json-'.($event->id ?? $loop->index);
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
                                        @elseif(!empty($metaRaw))
                                            <div class="meta">
                                                {{-- PT: JSON (raw) em acordeão colapsável para visualização sob demanda --}}
                                                {{-- EN: Raw JSON in collapsible accordion for on-demand viewing --}}
                                                @php
                                                    $rawCollapseId = 'admin-meta-raw-'.($event->id ?? $loop->index);
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <h4>{{ __('Eventos de Integração') }}</h4>
                @if($events2->isEmpty())
                    <div class="alert alert-secondary">{{ __('Nenhum evento de integração registrado para este contrato.') }}</div>
                @else
                    <div class="timeline">
                         @foreach($events2 as $event)
                            <div class="timeline-item">
                                <div class="timeline-time">{{ $event->created_at->format('d/m/Y H:i') }}</div>
                                <div class="timeline-title">
                                    @if($event->event_type === 'integracao_sulamerica')
                                        <span class="badge badge-primary">Integração SulAmérica</span>
                                    @elseif($event->event_type === 'reativacao')
                                        <span class="badge badge-success">Reativação</span>
                                    @elseif($event->event_type === 'cancelamento' || $event->event_type === 'cancelamento_end')
                                        <span class="badge badge-danger">Cancelamento</span>
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
                                            <div class="meta">
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
                                                {{-- PT: JSON (array) em acordeão colapsável para visualização sob demanda --}}
                                                {{-- EN: Array JSON in collapsible accordion for on-demand viewing --}}
                                                @php
                                                    $arrCollapseId = 'admin-meta-json-'.($event->id ?? $loop->index).'-'.$event->event_type;
                                                @endphp
                                                {{-- Botão deve aparecer apanas para usuarios com id_permission=1 --}}
                                                @if(auth()->user()->id_permission == 1)
                                                    <p class="mb-2">
                                                        <button class="btn btn-xs btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $arrCollapseId }}" aria-expanded="false" aria-controls="{{ $arrCollapseId }}">
                                                            {{ __('Ver JSON') }}
                                                        </button>
                                                    </p>
                                                @endif
                                                <div id="{{ $arrCollapseId }}" class="collapse">
                                                    <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($metaArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </div>
                                        @elseif(!empty($metaRaw))
                                            <div class="meta">
                                                {{-- PT: JSON (raw) em acordeão colapsável para visualização sob demanda --}}
                                                {{-- EN: Raw JSON in collapsible accordion for on-demand viewing --}}
                                                @php
                                                    $rawCollapseId = 'admin-meta-raw-'.($event->id ?? $loop->index);
                                                @endphp
                                                {{-- Botão deve aparecer apanas para usuarios com id_permission=1 --}}
                                                @if(auth()->user()->id_permission == 1)
                                                    <p class="mb-2">
                                                        <button class="btn btn-xs btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $rawCollapseId }}" aria-expanded="false" aria-controls="{{ $rawCollapseId }}">
                                                            {{ __('Ver JSON') }}
                                                        </button>
                                                    </p>
                                                @endif
                                                <div id="{{ $rawCollapseId }}" class="collapse">
                                                    <pre style="white-space: pre-wrap; word-break: break-word;">{{ is_scalar($metaRaw) ? (string) $metaRaw : json_encode($metaRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos de timeline compatíveis com layout card */
.timeline { position: relative; padding: 10px 0; }
.timeline::before { content: ''; position: absolute; left: 25px; top: 0; bottom: 0; width: 2px; background: #e0e0e0; }
.timeline-item { position: relative; margin-left: 60px; margin-bottom: 12px; }
.timeline-item::before { content: ''; position: absolute; left: -35px; top: 5px; width: 12px; height: 12px; background: #3c8dbc; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #3c8dbc; }
.timeline-time { color: #888; font-size: 0.9rem; }
.timeline-title { font-weight: 600; margin-bottom: 4px; }
.meta { font-size: 0.9rem; background: #f9f9f9; padding: 8px; border-radius: 4px; }

/* PT: Estilos de impressão para a timeline do admin */
/* EN: Print styles for the admin timeline */
@media print {
  .btn, .no-print { display: none !important; }
  .timeline::before, .timeline-item::before { display: none !important; }
  .timeline { padding: 0; }
  .timeline-item { margin-left: 0; page-break-inside: avoid; }
  .card { box-shadow: none; border: 1px solid #ddd; }
  .meta { background: #fff; }
  .timeline-time { color: #000; font-size: 10pt; }
  .timeline-title { font-size: 12pt; }
  .table { font-size: 10pt; }
  pre { font-size: 9pt; }
  .collapse { display: none !important; }
}
</style>
