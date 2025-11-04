<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\admin\attachment;
use App\Models\admin\bidding_categorie;
use App\Models\admin\Biddings;
use App\Models\admin\bidding_genres;
use App\Models\admin\bidding_phase;
use App\Qlib\Qlib;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BiddingsController extends Controller
{
    public function index(Request $request)
    {
        // $dt = attachment::where('bidding_id',3)->select(['id', 'title', 'file_file_name as file_name', 'order', 'bidding_id'])->with('file_path')->orderBy('order', 'ASC')->get();
        $limit = false;
        $page = 0;
        if($request->has('title') && trim($request->get('title')) !== ""){
            $biddings = Biddings::where('title', 'LIKE', '%'.$request->get("title").'%')->orderBy('opening', 'DESC');
            $biddings = $biddings->orWhere('object', 'LIKE', '%'.$request->get("title").'%');
        }else{
            $biddings = Biddings::orderBy('opening', 'DESC');
        }
        if($request->has('year') && trim($request->get('year')) !== "")
            $biddings = $biddings->where(['year' => $request->get('year')]);
        if($request->has('genre') && trim($request->get('genre')) !== "")
            $biddings = $biddings->where(['genre_id' => $request->get('genre')]);
        if($request->has('phase') && trim($request->get('phase')) !== "")
            $biddings = $biddings->where(['phase_id' => $request->get('phase')]);
        if($request->has('category') && trim($request->get('category')) !== "")
            $biddings = $biddings->where(['bidding_category_id' => $request->get('category')]);
        if($request->has('date_begin'))
            $biddings = $biddings->whereDate('opening', '>=', Carbon::createFromFormat('Y-m-d', $request->get("date_begin"))->toDateString() );
        if($request->has('date_end'))
            $biddings = $biddings->whereDate('opening', '<=', Carbon::createFromFormat('Y-m-d', $request->get("date_end"))->toDateString() );
        $count = $biddings->count();
        if($request->has('limit'))
            $limit = $request->get('limit');
        if($request->has('page'))
            $page = $request->get('page') - 1;
        if($limit)
            $biddings = $biddings->take($limit)->skip($limit * $page);

        $phases =  bidding_phase::select(['id', 'name'])->orderBy('name', 'asc')->get();
        $genres =  bidding_genres::select(['id', 'name'])->orderBy('name', 'asc')->get();
        $bidding_categories = bidding_categorie::select(['id', 'name'])->orderBy('name', 'asc')->get();
        $anos = Qlib::sql_distinct('biddings','year','ano');
        // dd($anos);
        $biddings = $biddings->where(['active' => 's'])
            ->orderBy('opening', 'desc')
            ->select(['id', 'title','subtitle', 'opening', 'indentifier','year', 'object', 'genre_id', 'phase_id', 'bidding_category_id', 'created_at','active'])
            ->with('genre')
            ->with('phase')
            ->with('category')
            ->with('attachments')
            ->get();
        return [ "amount" => $count, 'biddings' => $biddings, 'phases' => $phases, 'genres' => $genres, 'bidding_categories' => $bidding_categories,'anos'=>$anos];
    }
}
