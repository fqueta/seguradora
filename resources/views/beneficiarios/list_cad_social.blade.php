@if(isset($config['cad_social']))
    <div class="card card-secondary">
        <div class="card-header">
            {{__('Cadastro social')}}
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            {{__('Id')}}
                        </th>
                        <th>
                            {{__('Quadra')}}
                        </th>
                        <th>
                            {{__('Lotes')}}
                        </th>
                        <th class="text-right">
                            ...
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($config['cad_social'] as $vl)
                    <tr>
                        <td>
                            {{$vl['id']}}
                        </td>
                        <td>
                            {{$vl['n_quadra']}}
                        </td>
                        <td>
                            {{$vl['lotes']}}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('familias.show',$vl['id']) }}?redirect={{ route('beneficiarios.show',$config['id']) }}" class="btn btn-default" rel="noopener noreferrer"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- <div class="card-footer text-muted">
            Footer
        </div> --}}
    </div>
@endif
