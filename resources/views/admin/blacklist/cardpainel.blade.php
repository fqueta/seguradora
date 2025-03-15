<style>
    .dataTables_filter{
        padding-right: 10px;
    }
    .dataTables_info{
        padding: 0 0 10px 10px
    }
</style>
@php
    $card_id = 'back-list';
@endphp
@if (isset($config['blacklist']) && is_array($config['blacklist']))
    <div class="col-md-6" id="{{$card_id}}">
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">{{__('Clientes no Black List')}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table-blacklist-home" class="table m-0 dataTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Situação</th>
                                {{-- <th>Ação</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($config['blacklist'] as $k=>$v )
                                @php
                                    $link = url('/admin/users/'.$v['id'].'/show?redirect=').App\Qlib\Qlib::UrlAtual().'#'.$card_id;
                                @endphp
                                <tr>
                                    <td><a href="{{$link}}">{{$v['id']}}</a></td>
                                    <td><a href="{{$link}}">{{$v['name']}}</a></td>
                                    <td>
                                        <div class="sparkbar" data-color="#00a65a" data-height="20">{{$v['cpf']}}</div>
                                    </td>
                                    <td><span class="badge badge-danger">Suspenso</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
    </div>
@endif
