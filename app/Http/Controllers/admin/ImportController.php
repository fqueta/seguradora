<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        Excel::import(new UsersImport, $request->file('file'));

        return back()->with('success', 'Importação concluída!');
    }
    public function form_import(Request $request){
        $d = $request->all();
        return view('clientes.import',$d);
    }
}
