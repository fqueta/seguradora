<link rel="stylesheet" href="{{url('/')}}/css/dropzone.min.css" type="text/css" />
<script src="{{url('/')}}/js/dropzone.min.js"></script>
<!-- Button trigger modal -->
<div class="row">
    <div class="col-md-12 mb-2">
        <input type="hidden" id="dados-lista-files" value="{{$listFilesCode}}">
        <input type="hidden" id="tenant_asset" value="{{tenant_asset('/')}}">
        <span id="lista-files">
            {{-- {{App\Qlib\Qlib::gerUploadAquivos([
                'parte'=>'lista',
                'token_produto'=>$config['token_produto'],
                'tipo'=>'list',
                'listFiles'=>@$config['listFiles'],
                'routa'=>@$config['routa'],
                'url'=>@$config['url'],
                'arquivos'=>@$config['arquivos'],
                ])}} --}}

        </span>
    </div>
    <div class="col-md-12">
        {{-- {{dd($config)}} --}}
        @can('create',$config['route'])
            @if (isset($config['arquivos']) && $config['arquivos'])
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modelId"> <i class="fas fa-upload"></i>
                    {{ __('Enviar arquivos') }}
                </button>
            @endif
        @endcan

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Uploads de arquivos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="file-upload" action="{{route('uploads.store')}}" method="post" class="dropzone" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="bidding_id" value="{{@$config['bidding_id']}}" />
                        <input type="hidden" name="pasta" value="{{$config['pasta']}}" />
                        <input type="hidden" name="arquivos" value="{{$config['arquivos']}}" />
                        <input type="hidden" name="typeN" value="{{@$config['typeN']}}" />
                        <input type="hidden" name="local" value="{{@$config['local']}}" />
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"  onclick="visualizaArquivos2('{{$config['bidding_id']}}','{{route('uploads.index')}}')" data-dismiss="modal">{{__('Fechar')}}</button>
            </div>
        </div>
    </div>
</div>
