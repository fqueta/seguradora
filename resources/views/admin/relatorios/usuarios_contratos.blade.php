@extends('adminlte::page')

@section('title', 'Relatórios - Usuários com Contratos')

@section('content_header')
    <h1>Relatórios: Usuários com Contratos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Formulário de filtros / Filters form -->
            <form method="GET" action="{{ route('relatorios.usuarios_contratos.index') }}" class="row g-3">
                <!-- Preserva a busca do topo (AdminLTE) -->
                <input type="hidden" name="adminlteSearch" value="{{ request('adminlteSearch') }}" />
                <div class="col-md-3">
                    <label class="form-label">Campo do período</label>
                    <select name="campo_periodo" class="form-select form-control">
                        <option value="inicio" {{ (request('campo_periodo','inicio')=='inicio') ? 'selected' : '' }}>Data de início</option>
                        <option value="fim" {{ (request('campo_periodo')=='fim') ? 'selected' : '' }}>Data de fim</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Período inicial</label>
                    <input type="date" name="periodo_inicio" value="{{ request('periodo_inicio') }}" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Período final</label>
                    <input type="date" name="periodo_fim" value="{{ request('periodo_fim') }}" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status do contrato</label>
                    <select name="status" class="form-select form-control">
                        <option value="">Todos</option>
                        @if(!empty($statusOptions))
                            @foreach($statusOptions as $value => $nome)
                                <option value="{{ $value }}" {{ (request('status')==$value) ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Autor do contrato</label>
                    <select name="autor" class="form-select form-control">
                        <option value="">Todos</option>
                        @if(!empty($authorsOptions))
                            @foreach($authorsOptions as $id => $nome)
                                <option value="{{ $id }}" {{ (request('autor')==$id) ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end mt-2">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a class="btn btn-success" href="{{ route('relatorios.usuarios_contratos.export', [
                        'campo_periodo'=>request('campo_periodo','inicio'),
                        'periodo_inicio'=>request('periodo_inicio'),
                        'periodo_fim'=>request('periodo_fim'),
                        'status'=>request('status'),
                        'adminlteSearch'=>request('adminlteSearch'),
                        'autor'=>request('autor'),
                    ]) }}">Exportar Excel</a>
                    <a class="btn btn-outline-secondary me-2" href="{{ route('relatorios.usuarios_contratos.index') }}">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <div class="p-3">
                <strong>Registros:</strong>
                Exibindo {{ $registros->count() }} de {{ $registros->total() }}
                (Página {{ $registros->currentPage() }} de {{ $registros->lastPage() }})
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Data de inicio</th>
                        <th>Data de Fim</th>
                        <th>Cancelamento</th>
                        <th>Nome</th>
                        <th>Autor</th>
                        <th>CPF</th>
                        <th>Data de Nascimento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $item)
                        <tr>
                            <td>{{ $item['contrato_inicio'] }}</td>
                            <td>{{ $item['contrato_fim'] }}</td>
                            <td>{{ $item['data_cancelamento'] ?? '' }}</td>
                            <td>
                                <a class="nome-link" href="{{ route('clientes.show', ['id' => $item['id'] ?? null]) }}?redirect={{ urlencode(url()->full()) }}">
                                    {{ $item['nome'] }}
                                </a>
                            </td>
                            <td>{{ $item['autor'] ?? '' }}</td>
                            <td>{{ $item['cpf'] }}</td>
                            <td>{{ $item['nascimento'] ?? '' }}</td>
                            <td>
                                @php
                                    $st = $item['status'] ?? '';
                                    $cls = 'badge badge-secondary';
                                    if ($st === 'Aprovado' || $st === 'aprovado') {
                                        $cls = 'badge badge-success';
                                    } elseif ($st === 'Cancelado' || $st === 'cancelado') {
                                        $cls = 'badge badge-danger';
                                    } elseif ($st === 'Reativando' || $st === 'reativando') {
                                        $cls = 'badge badge-warning';
                                    } elseif (!empty($st)) {
                                        $cls = 'badge badge-info';
                                    }
                                @endphp
                                @if(!empty($st))
                                    <span class="{{ $cls }}">{{ $st }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhum registro encontrado para os filtros informados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $registros->links() }}
        </div>
    </div>
@stop

@section('css')
    @include('qlib.csslib')
    <style>
        /* Sublinha o nome para destacar que é um link */
        .nome-link { text-decoration: underline; }
    </style>
@stop
@section('js')
    @include('qlib.jslib')

    <script>
        $(function(){
            $('[exportar-filter]').on('click',function(e){
                e.preventDefault();
                var urlAtual = window.location.href;
                var d = urlAtual.split('?');
                url = '';
                if(d[1]){
                    url = $(this).attr('href');
                    url = url+'?'+d[1];
                }
                if(url)
                    abrirjanelaPadrao(url);
                    //window.open(url, "_blank", "toolbar=1, scrollbars=1, resizable=1, width=" + 1015 + ", height=" + 800);
                //confirmDelete($(this));
            });
            $('[data-del="true"]').on('click',function(e){
                e.preventDefault();
                confirmDelete($(this));
            });
            $('[name="filter[cpf]"],[name="filter[cpf_conjuge]"]').inputmask('999.999.999-99');
            $(' [order="true"] ').on('click',function(){
                var val = $(this).val();
                var url = lib_trataAddUrl('order',val);
                window.location = url;
            });
        });
    </script>
  @stop

