@if (isset($dados) && $dados)
    <div class="col-md-12">

        <table class="table">
            <thead>
                <tr>
                    <th>
                        {{__('Protocolo')}}
                    </th>
                    <th>
                        {{__('Assunto')}}
                    </th>
                    <th>
                        {{__('Status')}}
                    </th>
                    <th>
                        {{__('Motivos')}}
                    </th>
                    <th>
                        {{__('Ação')}}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dados as $k=>$v)
                @php
                    $cofg = App\Qlib\Qlib::lib_json_array($v['config']);
                    $assunto = isset($cofg['categoria'])?$cofg['categoria']:false;
                    $motivo = false;
                    $status = false;
                    $tr_class=false;
                    if(isset($_GET['idCad'])&&$_GET['idCad']==$v['id']){
                        $tr_class='table-info';
                    }
                    if($assunto){
                        $assunto = @$arr_categorias[$assunto];
                    }
                    if($v['motivo']){
                        $motivo = @$arr_motivos[$v['motivo']];
                    }
                    if($v['status']){
                        $status = @$arr_status[$v['status']];
                    }
                    @endphp
                <tr class="{{$tr_class}}">
                    <td>
                        {{$v['protocolo']}}
                    </td>
                    <td>
                        {{$assunto}}
                    </td>
                    <td>
                        {{$status}}
                    </td>
                    <td>
                        {{$motivo}}
                    </td>
                    <td>
                        <a href="{{route('sic.show',['id'=>$v['id']])}}" title="Detalhes" class="btn btn-secondary"><i class="fas fa-eye    "></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-12">
        @if ($config['limit']!='todos')
            {{ $dados->appends($_GET)->links() }}
        @endif
    </div>
@endif
