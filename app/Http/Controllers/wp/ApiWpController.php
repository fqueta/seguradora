<?php

namespace App\Http\Controllers\wp;

use App\Http\Controllers\Controller;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class ApiWpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $key;
    private $url_base;
    private $url_api;
    private $url;
    private $wp_ep; //Wp EndPoint
    public function __construct()
    {
        //$this->key = 'ZmVybmFuZG9AbWFpc2FxdWkuY29tLmJyOmZlcnF1ZXRh';
        $this->key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL3dvcmRwcmVzcyIsImlhdCI6MTY1NDUyNzI4MSwibmJmIjoxNjU0NTI3MjgxLCJleHAiOjE2NTUxMzIwODEsImRhdGEiOnsidXNlciI6eyJpZCI6MSwiZGV2aWNlIjoiIiwicGFzcyI6ImEwMDBlMDQ5ZmQyZjhhYTczYTY3NjUwMzU3YzYyMmM1In19fQ.XDx8qlt8GOkASzHQPtqpuzji5fr-VCuy0Xll-Be2gUA';
        $this->url_base = 'http://localhost/wordpress/';
        //$this->url_api = 'wp-json/wp/v2';
        $this->url_api = 'wp-json/api-cms';
        $this->url = $this->url_base.$this->url_api;
        $this->wp_ep = isset($_POST['wp_ep'])?$_POST['wp_ep']:'galeria';
    }
    public function exec($config = null)
    {
        /*
            $api_wp = new ApiWpController([
                'endPoint'=>'posts',
                'method'=>'POST',
                'params'=>[
                    'post_title'=>'Titulo do post',
                    'post_name'=>'nome-do-post',
                    'content'=>'conteudo',
                    'featured_media'=>'1', //foto de capa
                ]
            ]);
         */
        $endPoint = isset($config['endPoint'])?$config['endPoint']:$this->wp_ep;
        $method = isset($config['method'])?$config['method']:'GET';
        $params = isset($config['params'])?$config['params']:false;
        if($params && is_array($params))
            $params = '?'.http_build_query($params);
        $url = $this->url.'/'.$endPoint.$params;
        //dd($url);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->key
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $ret['json'] = $response;
        $ret['arr'] = Qlib::lib_json_array($response);
        return $ret;
    }
    public function exec2($config = null)
    {
        /*
            $api_wp = new ApiWpController([
                'endPoint'=>'posts',
                'method'=>'POST',
                'params'=>[
                    'post_title'=>'Titulo do post',
                    'post_name'=>'nome-do-post',
                    'content'=>'conteudo',
                    'featured_media'=>'1', //foto de capa
                ]
            ]);
         */
        $endPoint = isset($config['endPoint'])?$config['endPoint']:$this->wp_ep;
        $method = isset($config['method'])?$config['method']:'GET';
        $params = isset($config['params'])?$config['params']:false;
        $url = $this->url.'/'.$endPoint;
        $json = json_encode($params);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS =>$json,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $ret['json'] = $response;
        $ret['arr'] = Qlib::lib_json_array($response);
        if($response){
            $ret['exec'] = true;
        }else{
            $ret['exec'] = false;
        }
        $strtotime = 1655132081;

        $ret['ex'] = date('d M Y H:i:s',$strtotime);

        return $ret;
    }
    public function list($config = null)
    {
        /*
            $api_wp = new ApiWpController([
                'endPoint'=>'posts',
                'method'=>'GET',
                'params'=>''
            ]);
         */
        $endPoint = isset($config['endPoint'])?$config['endPoint']:'post';
        $method = isset($config['method'])?$config['method']:'GET';
        $params = isset($config['params'])?$config['params']:false;
        $url = $this->url.'/'.$endPoint.$params;
        //$json = json_encode($params);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $ret['json'] = $response;
        $ret['arr'] = Qlib::lib_json_array($response);
        if($response){
            $ret['exec'] = true;
        }else{
            $ret['exec'] = false;
        }
        return $ret;
    }
    public function delete($config = null)
    {
        /*
            $api_wp = new ApiWpController([
                'endPoint'=>'post',
                'method'=>'GET',
                'params'=>'/{id_post}'
            ]);
         */
        $endPoint = isset($config['endPoint'])?$config['endPoint']:'post';
        $method = isset($config['method'])?$config['method']:'DELETE';
        $params = isset($config['params'])?$config['params']:false;
        $url = $this->url.'/'.$endPoint.$params;
        //$json = json_encode($params);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $ret['json'] = $response;
        $ret['arr'] = Qlib::lib_json_array($response);
        if($response){
            $ret['exec'] = true;
        }else{
            $ret['exec'] = false;
        }
        return $ret;
    }
    public function postFiles($config = null)
    {
        /*
            $api_wp = new ApiWpController([
                'endPoint'=>'posts',
                'method'=>'POST',
                'params'=>[
                    'post_title'=>'Titulo do post',
                    'post_name'=>'nome-do-post',
                    'content'=>'conteudo',
                    'featured_media'=>'1', //foto de capa
                ]
            ]);
         */
        $endPoint = isset($config['endPoint'])?$config['endPoint']:'midia';
        //$endPoint = isset($config['endPoint'])?$config['endPoint']:'geleria';
        $method = isset($config['method'])?$config['method']:'POST';
        $params = isset($config['params'])?$config['params']:false;
        $filename = isset($config['filename'])?$config['filename']:false;
        $file = isset($config['file'])?$config['file']:false;
        $type = isset($config['type'])?$config['type']:'jpeg';
        if($params && is_array($params))
            $params = '?'.http_build_query($params);
            $url = $this->url.'/'.$endPoint.$params;
        //$url = 'http://localhost/wordpress/wp-json/api/midia';
        //echo $url;
        //exit;
        if($file){
            $cfile = new \CURLFile($_FILES['file']['tmp_name'],$_FILES['file']['type'],$_FILES['file']['name']);
            $data = array('file'=>$cfile);
            $data['post_id'] = isset($_POST['post_id'])?$_POST['post_id']:NULL;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$this->key
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $ret['json'] = $response;
            $ret['arr'] = Qlib::lib_json_array($response);
        }else{
            $ret['mens'] = __('Arquivo inválido');
        }
        return $ret;
    }
    public function index()
    {
        $ret = $this->exec([
            'endPoint'=>'posts'
        ]);
        //dd($ret);
        return $ret;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = $request->all();
        $file = $request->file('file');
        $ret = false;
        if(isset($post['wp_ep']) && isset($_FILES['file']) && $file){
            if($post['wp_ep']=='midia'){
                $filenameWithExt = $file->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // Get just ext
                $extension = $file->getClientOriginalExtension();
                // Filename to store
                $typeN = isset($post['typeN']) ? $post['typeN'] : 2;
                if($typeN==1){
                    $fileNameToStore= $filename.'_'.time().'.'.$extension;
                }else{
                    $fileNameToStore= $filename.'.'.$extension;
                }
                $arquivos = isset($post['arquivos']) ? $post['arquivos'] : 'jpg,jpeg,png';
                if($arquivos){
                    $arr_extension = explode(',',$arquivos);
                }
                //if($extension=='jpg' || $extension=='jpeg' || $extension=='png' || $extension=='zip' || $extension=='pdf' || $extension=='PDF'){
                if(in_array($extension,$arr_extension)){
                    $ret = $this->postFiles([
                        'filename'=>$fileNameToStore,
                        'file'=>$_FILES['file'],
                        'type'=>$extension,
                    ]);
                }
            }
        }
        return $ret;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
