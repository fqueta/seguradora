<div class="col-md-12">
    @if (isset($dados['post_type']) && ($type=$dados['post_type']) && isset($dados['post_name']))
        @php
            $slug=$dados['post_name'];
            $raiz_site = '/';
            $link = $raiz_site;
        @endphp
        @if ($type=='paginas')
            @php
                $link .= $slug
            @endphp
            <a href="{{$link}}" target="_blank" >{{$link}}</a>
        @endif
    @endif
</div>
