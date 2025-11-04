<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $user;
    public $tab;
    public $title;
    public $routa;
    public function __construct()
    {
        $this->middleware('auth');
        $this->user = Auth::user();
        $this->tab = 'home';
        $this->title = 'Dashboard ' ;
        $this->routa = 'painel';
    }

    public function teste(){
      //$dados = $request->all();
      //var_dump($dados);
      return view('teste');
    }
    public function upload(Request $request){
      $dados = $request->all();
      var_dump($dados);
    }


    public function index(Request $request)
    {
        $this->authorize('ler', $this->routa);
        // $controlerFamilias = new FamiliaController(Auth::user());
        // $controlerMapas = new MapasController(Auth::user());
        // $dadosFamilias = $controlerFamilias->queryFamilias();
        // $id_quadra_home = Qlib::qoption('id_quadra_home')?Qlib::qoption('id_quadra_home'):@$_GET['id_qh'];
        // if($id_quadra_home){
        //     $dadosMp = $controlerMapas->queryQuadras($id_quadra_home);
        // }else{
        //     $dadosMp = false;
        // }
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
        // $totalDecretos = Post::where('post_type','=','decreto')->count();
        $config = [
            // 'c_familias'=>$dadosFamilias,
            // 'mapa'=>$dadosMp,
            // 'totalDecretos'=>$totalDecretos,
        ];
        // dd($request->all());
        return view('home',[
            'config'=>$config,
        ]);
    }

}
