<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\_upload;
use App\Models\Post;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class mediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $ret['exec'] = false;
        $ret['select_file'] = isset($_GET['select_file'])?$_GET['select_file']:'unique'; //unique or multiple
        $ret['field_media'] = isset($_GET['field_media'])?$_GET['field_media']:'post_parent'; //unique or multiple
        if($request->has('token_produto')){
            $arquivos = _upload::where('token_produto','=',$request->get('token_produto'))->get();
            if($arquivos){
                $ret['exec'] = true;
                $ret['arquivos'] = $arquivos;
            }
        }else{
            $arquivos = Post::where('post_type','=','attachment')->where('post_status','=','publish')->get();
            if($arquivos->count()){
                $ret['exec'] = true;
                $ret['arquivos'] = $arquivos;
            }
        }
        if($request->has('ajax')){
            if($request->get('ajax')=='s'){
                return response()->json($ret);
            }else{
                return false;
            }
        }else{
            return view('admin.media.index',$ret);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeParent(Request $request)
    {
        $d = $request->all();
        // se o type select for radio é unico tem que salvar post_id no post_parent.
        $ret['exec'] = false;
        $type_select = isset($d['type_select']) ? $d['type_select'] : 'radio';
        if(isset($d['post_id']) && isset($d['post_parent']) && ($id=$d['post_parent'])){
            if($type_select=='radio'){
                $ds = ['post_parent' => $d['post_id']];
                $save_media = Qlib::qoption('save_media')?Qlib::qoption('save_media'):'a'; // a para entes dou d para depois
                $ret['save_media'] = $save_media;
                $link_img = Qlib::buscaValorDb([
                    'tab'=>'posts',
                    'campo_bus'=>'ID',
                    'valor'=>$d['post_id'],
                    'select'=>'guid',
                    'compleSql'=>''
                ]);
                $ret['post_id'] = $d['post_id'];
                if($save_media=='d'){
                    $ret['exec'] = true;
                }elseif($save_media=='a'){
                    $ret['exec'] = Post::where('ID',$id)->update($ds);
                }
                if($link_img){
                    $ret['link_img'] = \tenant_asset($link_img);
                }

            }
        }
        return $ret;
    }
    public function trash(Request $request)
    {
        $d = $request->all();
        $ret['exec'] = false;
        if(isset($d['post_id'])){

            $ds = ['post_status' => 'trash'];
            $ret['exec'] = Post::where('ID',$d['post_id'])->update($ds);
        }
        return $ret;
    }
    public function create(Request $request)
    {

    }

    public function store(Request $request)
    {
        $file = $request->file('file');
        $filenameWithExt = $file->getClientOriginalName();
        // Get just filename
        $mimeType = $file->getClientMimeType();
        // Get mimetype
        // dd($mimeType);
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Get just ext
        $extension = $file->getClientOriginalExtension();
        // Filename to store
        $typeN = isset($request->typeN) ? $request->typeN : 2;
        if($typeN==1){
            $fileNameToStore= $filename.'_'.time().'.'.$extension;
        }else{
            $fileNameToStore= $filename.'.'.$extension;
        }
        $fileNameToStore = strip_tags($fileNameToStore);
        $fileNameToStore = str_replace('{', '', $fileNameToStore);
        $fileNameToStore = str_replace('}', '', $fileNameToStore);
        $arquivos = isset($request->arquivos) ? $request->arquivos : 'jpg,jpeg,png,zip,pdf,PDF,JPG';
        if($arquivos){
            $arr_extension = explode(',',$arquivos);
        }
        if(in_array($extension,$arr_extension)){
            $dados = $request->all();
            //$token_produto = $dados['token_produto'];
            // $ultimoValor = _upload::where('token_produto','=',$token_produto)->max('ordem');
            // $ordem = $ultimoValor ? $ultimoValor : 0;
            // $ordem++;
            $pasta = isset($dados['pasta'])?$dados['pasta']:'media/'.date('Y').'/'.date('m');
            $post_status = isset($dados['post_status'])?$dados['post_status']:'publish';
            $nomeArquivoSavo = $file->storeAs($pasta,$fileNameToStore);
            $exec = false;
            $salvar = false;
            if($nomeArquivoSavo){
                $exec = true;
                $dataLocalDb = Qlib::dataLocalDb();
                $dataSalv = [
                    'post_author'=>Auth::id(),
                    'post_date'=>date('Y-m-d'),
                    'post_date_gmt'=>$dataLocalDb,
                    'post_name'=>$fileNameToStore,
                    'post_modified'=>$dataLocalDb,
                    'post_modified_gmt'=>$dataLocalDb,
                    'guid'=>$nomeArquivoSavo,
                    'post_type'=>'attachment',
                    'post_mime_type'=>$mimeType,
                    'post_status'=>$post_status,
                    'config'=>json_encode(['extenssao'=>$extension])
                ];
                $salvar = Post::create($dataSalv);
            }
            //$lista = _upload::where('token_produto','=',$token_produto)->get();
            if($salvar){
                return response()->json(['Arquivo enviado com sucesso'=>200]);
            }
        }else{
            return response()->json('O Formato .'.$extension.' não é permitido', 400);
        }
    }
    public function libMediaPanel($config=false){
        $ret = false;
        $post = Post::where('post_type', '=','attachment')->where('post_status', '=','publish')->get();
        return $ret;
    }
}
