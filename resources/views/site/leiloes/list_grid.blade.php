
<div class="items-list mb-4">
    @php
    $li = false;
    $class_lc = new App\Http\Controllers\LeilaoController;
    @endphp
    @if (is_object($dados) || is_array($dados))
        @foreach ($dados as $k=>$v )
            @php
                // $v['src'] = App\Qlib\Qlib::get_thumbnail_link($v['ID']);
                // $v['link'] = App\Qlib\Qlib::get_the_permalink($v['ID'],$v);
                // $v['link_edit_admin'] = (new App\Http\Controllers\LeilaoController)->get_link_edit_admin($v['ID'],$v);
                $info_termino = $class_lc->info_termino($v['ID']);
                $li .= view('site.leiloes.grid',[
                    'v'=>$v,
                    'info_termino'=>$info_termino['html'],
                    'arr_termino'=>$info_termino
                ]);
            @endphp
        @endforeach
        @php
            echo $li;
        @endphp
    @endif
</div>
