<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct()
    {

    }
    public function home(){
        return redirect()->route('home.admin') ;
        // return view('portal.sic_front.index');
    }
    public function test_portal(Request $request){
        $title = 'Pagina de teste do site';
        return view('portal.testes.index',['title' => $title]);
    }
}
