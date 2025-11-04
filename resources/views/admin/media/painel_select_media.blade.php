@php
    if(isset($config['media'])){
        $files_types = $config['media']['files']; // tipos de arquivos
        $select_type = $config['media']['select_files']; // tipo de seleção Multipla ou unica
        $field_media = $config['media']['field_media']; //campo onde salva os ids dos arquivos
        $post_parent = $config['media']['post_parent']; //campo onde salva os ids dos arquivos
    }
    $ac = isset($ac) ? $ac : 'modal'; //Modal para imagem destacada
@endphp
@if($ac=='view')
<form id="imagem-detacada" action="">
    <div class="input-group mb-3" style="max height: 250px;min-height:200px">
        <img id="media-{{@$value['post_parent']}}" style="width:100%;" src="{{@$value['imagem_destacada']}}">
    </div>
    <div class="input-group">
        <span class="input-group-btn">
            <a id="lfm" data-input="thumbnail" onclick="modalMediaInstert();" data-preview="holder" class="btn btn-outline-primary" >
                <i class="fas fa-file-image"></i> {{__('Inserir')}}
            </a>
            @php
                $display_btn_remove = 'd-none';
                if (isset($value['imagem_destacada']) && !empty($value['imagem_destacada'])){
                    $display_btn_remove = '';
                }
            @endphp
                <a id="remove-img-destaque" onclick="lib_mediaRemove('{{$value['post_parent']}}','radio');" class="btn btn-outline-danger {{$display_btn_remove}}">
                    <i class="fas fa-trash" ></i> {{__('Remover')}}
                </a>
            {{-- <a id="lfm-remove" class="btn btn-outline-danger d-none">
                <i class="fas fa-trash" onclick="lib_removeImageLfm(this);"></i> {{__('Remover')}}
            </a> --}}

        </span>
        <input id="thumbnail" onchange="lib_carregaImageLfm(this)" class="form-control" type="hidden" name="d_meta[meta_value]" value="{{@$value['imagem_destacada']}}">
        <input class="form-control" type="hidden" name="d_meta[meta_key]" value="imagem_destacada">
    </div>
</form>
@elseif($ac=='modal')
<!-- Modal -->
<div class="modal fade" id="painel-select-media" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">{{__('Selecione Mídia')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                    <link rel="stylesheet" href="{{url('/')}}/css/dropzone.min.css" type="text/css" />
                    <script src="{{url('/')}}/js/dropzone.min.js"></script>
                    <style>

                    </style>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="card text-center">
                      <div class="card-header">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                              <button class="nav-link active" id="upload-tab" data-toggle="tab" data-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="true" onclick="btnUpload();">Upload</button>
                            </li>
                            <li class="nav-item" role="presentation">
                              <button class="nav-link" id="library-tab" data-toggle="tab" data-target="#library" onclick="get_media_lib();" type="button" role="tab" aria-controls="library" aria-selected="false">{{__('Biblioteca')}}</button>
                            </li>
                          </ul>
                      </div>
                      <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                          <div class="tab-pane fade show active" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                            <p>Tipos de arquivos suportados: <b>{{$files_types}}</b></p>

                            <form id="file-upload" action="{{route('media.store')}}" method="post" class="dropzone" enctype="multipart/form-data">
                                @csrf
                                {{-- <input type="hidden" name="token_produto" value="{{$config['token_produto']}}" />
                                <input type="hidden" name="pasta" value="{{$config['pasta']}}" /> --}}
                                <input type="hidden" name="arquivos" value="{{$files_types}}" />
                                <input type="hidden" name="typeN" value="{{@$config['typeN']}}" />
                                <input type="hidden" name="post_parent" value="{{@$post_parent}}" />
                                <div class="fallback">
                                    <input name="file" type="file" multiple />
                                </div>
                            </form>
                          </div>
                          <div class="tab-pane fade text-left" id="library" role="tabpanel" aria-labelledby="library-tab"><span class="d-none" id="media-route">{{route('media.index')}}</span>
                            <span id="list-libarary">
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Fechar')}}</button>
                <button type="button" id="btn-lib" class="btn btn-primary d-none" onclick="getLibTab()" >{{__('Biblioteca')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function get_media_lib(){
        var lib_list = $('#library').html();
        var url_media = $('#media-route').html();
        if(url_media){
            getAjax({
                url:url_media,
                data:{
                    noajax:'n'
                },
                dataType:'html'
            },function(res){
                $('#preload').fadeOut("fast");
                $('#list-libarary').html(res);
                $('.media-files [type="checkbox"]').attr('type','radio').attr('name','select-media');
                // console.log(res);
            });
        }
    }
    function media_select(type,post_id){
        var pparent = $('#file-upload [name="post_parent"]').val();
        $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        getAjax({
            type:"POST",
            url:"{{route('store.parent.media')}}",
            data:{
                type_select:type,
                post_id:post_id,
                post_parent:pparent,
            },
        },function(res){
            $('#preload').fadeOut("fast");
            if(res.exec && res.link_img && res.post_id){
                if(type=='radio'){
                    $('#painel-select-media').modal('hide');
                    $('#imagem-detacada img').attr('src',res.link_img);
                    let post_id_input = '<input type="hidden" class="add-post-parent" name="post_parent" value="'+post_id+'"/>';
                    $('.add-post-parent').remove();
                    $(post_id_input).insertAfter('input[name="ID"]');
                    $('#remove-img-destaque').removeClass('d-none');
                }
            }
        });
    }

    function lib_mediaRemove(pp,type){
        if(typeof type=='undefined'){
            type='radio';
        }
        if(type=='radio'){
            $('#imagem-detacada img').attr('src','');
            let post_id_input = '<input type="hidden" class="add-post-parent" name="post_parent" value="0"/>';
            $('.add-post-parent').remove();
            $(post_id_input).insertAfter('input[name="ID"]');
            $('#remove-img-destaque').addClass('d-none');
        }
    }
    function trashMedia(id){
        if(!window.confirm('Deseja mesmo excluir')){
            return false;
        }
        $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        getAjax({
            type:"POST",
            url:"{{route('trash.media')}}",
            data:{
                post_id:id,
            },
        },function(res){
            $('#preload').fadeOut("fast");
            if(res.exec){
                $('[for="media-'+id+'"]').remove();
            }
        });
    }
    function modalMediaInstert(){
        $('#painel-select-media').modal({backdrop: 'static'});
        document.querySelector('#library-tab').click();
    }
    function btnUpload(){
        // $('[data-dismiss="modal"]').addClass('d-none');
        $('#btn-lib').removeClass('d-none');
    }
    function getLibTab(){
        document.querySelector('#library-tab').click();
    }
</script>
@endif
