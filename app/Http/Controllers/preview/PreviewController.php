<?php

namespace App\Http\Controllers\preview;

use App\Http\Controllers\admin\PostsController;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function posts($id,Request $request){
        $pc = new PostsController;
        if(is_string($id)){
            $id = $pc->get_id_by_slug($id);
        }
        $d = $pc->get_post($id);
        // dd($d);
        if($d->count()>0){
            $title = $d->post_title;
            $conteudo = $pc->short_code('posts-'.$id);
        }else{
            $title = 'Indisponivel';
            $conteudo = '<h2>Erro 404</h2>';
        }
        $ret = [
            'd'=>$d,
            'title'=>$title,
            'conteudo'=>Qlib::shortCode_html($conteudo),
        ];
        return view('preview.index',$ret);
    }
    public function noticias(Request $request){
        $ret = [
            'content'=>'Ola noticia mundo'
        ];
    }
}
