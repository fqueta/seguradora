<style>
    .grid-media-files img{
        height: 150px;
        max-height: 200px;
        object-fit: cover;
        object-position: center;
    }
</style>
<div class="card">
    <div class="card-header">
        {{__('Biblioteca de mídia')}}
    </div>
    <div class="card-body">
        @php
            $type_select = 'radio';
            if(isset($select_file)){
                if($select_file=='multiple'){
                    $type_select = 'checkbox';
                }
            }
        @endphp
        @if(isset($arquivos) && is_object($arquivos))
        {{-- {{dd($arquivos)}} --}}
            <div class="row media-files">
                @foreach ($arquivos as $k=>$v )
                <label class="col-md-2 col-6" for="media-{{$v['ID']}}">
                    <div class="row">
                        <div class="col-12">
                            @php
                                if($type_select=='radio'){
                                    $name_input = 'media_select_radio';
                                }else{
                                    $name_input = 'media-'.$v['ID'];
                                }
                            @endphp
                            <input type="{{$type_select}}" onclick="media_select('{{$type_select}}','{{$v['ID']}}')" name="{{$name_input}}" id="media-{{$v['ID']}}">
                            <span style="position: absolute;right:7px;z-index:1;top:26px;">
                                <button onclick="trashMedia('{{$v['ID']}}')" class="btn btn-outline-danger"><i class="fa fa-trash"></i></button>
                            </span>
                        </div>
                        <div class="col-12 grid-media-files">
                            <img class="w-100" src="/storage/{{$v['guid']}}" alt="{{$v['post_name']}}" />
                        </div>
                        {{-- <div class="col-12">
                            {{$v['post_name']}}
                        </div> --}}
                    </div>
                </label>
                @endforeach
            </div>
        @endif
    </div>
    <div class="card-footer text-muted">
    </div>
</div>
