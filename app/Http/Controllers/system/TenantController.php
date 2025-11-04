<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function create_all(Request $request)
    {
        $arr_tenant = $request->get('arr_tenant');
    }
    /**
     * Para criar multipos tenantes usar apenas quando for iniciar a plataforma
     * @param array $arr_tenants array com os dados dos tenantes que serão criados
     * @uso $ret = (new TenantController)->add_all(['id' =>'pf1','id' =>'pf1.localhost','name' =>'Prefeitura1']);
     */
    public function add_all($arr_tenant=[])
    {
        $tenant = false;
        // dd($arr_tenant);
        if(is_array($arr_tenant)){
            foreach ($arr_tenant as $k => $v) {
                //$v = ['id' => 'foo']
                $tenant1 = Tenant::create($v);
                $tenant1->domains()->create(['domain' => $v['domain']]);
                sleep(2);
            }
        }
        return $tenant;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
