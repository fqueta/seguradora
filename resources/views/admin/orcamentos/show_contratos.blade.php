@can('ler_arquivos',$routa)
    @php
        $aceito_termo = isset($config['assinatura']['aceito_termo']) ? $config['assinatura']['aceito_termo'] : 'n';
        $data = isset($config['assinatura']['data']) ? $config['assinatura']['data'] : '';
        $ip = isset($config['assinatura']['ip']) ? $config['assinatura']['ip'] : '';
        $arquivo_gerado = isset($config['gerado']['caminho']) ? $config['gerado']['caminho'] : '';
        $assinado = isset($config['assinado']['link']) ? $config['assinado']['link'] : '';
        $data_assinado = isset($config['assinado']['data']) ? $config['assinado']['data'] : '';
        $termo_enviado = false;
        $token = isset($value['token']) ? $value['token'] : '';
        // $signers = isset($config['zapsing']['response']['signers']) ? $config['zapsing']['response']['signers'] : false;
        $status_sing = isset($config['status_sing']) ? $config['status_sing'] : '';
        $assinantes = isset($config['assinantes']) ? $config['assinantes'] : false;
        $badge = 'badge-danger';
        if($status_sing=='signed'){
            $status_sing = __('Assinatura completa');
            $badge = 'badge-success';
        }elseif($status_sing=='pending'){
            $status_sing = 'Pendente';
        }
    @endphp
    {{-- @if ($aceito_termo == 's') --}}
        {{-- Exibir card de assinatura --}}

        <div class="card">
            <div class="card-header">
                {{__('Status de assinatura no zapsing')}}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        Status:
                        {{-- <table class="table">
                            <tbody>
                                <tr>
                                    <th>Status:</th>
                                    <td>{{$status_sing}}</td>
                                </tr>
                            </tbody>
                        </table> --}}
                    </div>
                    <div class="col-6 text-right">
                        <span class="badge {{$badge}}">
                            {!!$status_sing!!}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {{-- @if (is_array($assinantes) && !$status_sing) --}}
        @if (is_array($assinantes))
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    {{__('Gerenciamento de assinaturas')}}
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($assinantes as $k=>$v )
                        {{-- {{dd($v)}} --}}
                            @php
                                $status_sign = $v['status'];
                                $bdg = 'badge-danger';
                                if($status_sign=='signed'){
                                    $bdg = 'badge-success';
                                    $status_sign = 'Assinado';
                                }else{
                                    $status_sign = 'Aguardando Assinatura';
                                }
                            @endphp
                            <div class="card w-100">
                                <div class="card-body">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <b>Nome: </b> {{@$v['name']}}
                                            </div>
                                            <div class="col-md-6">
                                                <b>Visualizado: </b> {{@$v['times_viewed']}}
                                            </div>
                                            <div class="col-12 mb-2">
                                                <b>Status: </b> <span class="badge {{$bdg}}">{{$status_sign}}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1">{{__('Link de assinatura:')}}</span>
                                              </div>
                                            <input type="text" class="form-control" disabled value="{{$v['sign_url']}}" aria-label="Text input with dropdown button">
                                            <div class="input-group-append">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ação</button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="copyTextToClipboard('{{$v['sign_url']}}')">Copiar</a>
                                                <a class="dropdown-item" target="_blank" href="{{$v['sign_url']}}">Acessar</a>
                                                {{-- <a class="dropdown-item" href="#">Something else here</a>
                                                <div role="separator" class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#">Separated link</a> --}}
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- <div class="card-footer text-muted">
                    Footer
                </div> --}}
            </div>
        @endif
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{__('Arquivos')}}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="row">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            {{__('Ariquivo sem assinatura')}}
                                        </th>
                                        <th>
                                            {{__('Arquivo assinado')}}
                                        </th>
                                        <th>
                                            {{__('Ação')}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            @if ($arquivo_gerado)
                                                <a href="{{$arquivo_gerado}}" class="text-danger" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-file-pdf fa-2x"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($assinado)
                                            <div>
                                                <a href="{{url('/storage/'.$assinado)}}" class="text-danger" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-file-pdf fa-2x"></i>
                                                </a>
                                            </div>
                                            <div>
                                                <small>{{@$data_assinado}}</small>
                                            </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($termo_enviado || $assinado)
                                                <button class="btn">
                                                    <i>Enviado</i> <span class="badge badge-primary"></span>
                                                </button>
                                            @else
                                                {{-- Verficar se ja foi enviado --}}
                                                @if (!$assinantes)
                                                    <button class="btn btn-primary" onclick="envia_zapSing('{{$token}}')" title="Enviar para assinatura">
                                                        <i class="fas fa-envelope "></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- <div class="col-12">
                                <a href="{{route('termo.aceito',@$config['id'])}}?redirect_base={{base64_encode(App\Qlib\Qlib::UrlAtual())}}" title="Termo acento digitalmente em {{$data}}">
                                    <i class="fas fa-file-pdf fa-3x"></i>
                                </a>
                            </div>
                            <div class="col-12">
                                {{$data}}
                            </div>
                            <div class="col-12">
                                IP: {{$ip}}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- @endif --}}
@endcan
