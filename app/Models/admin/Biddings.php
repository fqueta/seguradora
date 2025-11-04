<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biddings extends Model
{
    use HasFactory;
    // protected $guarded  = ['id'];
    // protected $dates    = ['opening'];
    protected $table    = 'biddings';
    protected $casts = [
        'config' => 'array',
    ];
    protected $fillable = [
        'token',
        'genre_id',
        'phase_id',
        'year',
        'title',
        'subtitle',
        'indentifier',
        'description',
        'object',
        'active',
        'bidding_category_id',
        'order',
        'type_id',
        'author_id',
        'type_doc',
        'opening',
        'config',
        'excluido',
        // 'reg_excluido',
        // 'deletado',
        // 'reg_deletado',
    ];
    public function attachments()
    {
        $d = $this->hasMany(attachment::class,'bidding_id','id')->select(['id', 'title', 'file_file_name as file_name', 'order', 'bidding_id','file_config'])->orderBy('order', 'ASC');
        return $d;
    }
    public function genre()
    {
    	return $this->belongsTo(bidding_genres::class, 'genre_id', 'id')->select(['id', 'name']);
    }

    public function phase()
    {
    	return $this->belongsTo(bidding_phase::class, 'phase_id', 'id')->select(['id', 'name']);
    }

    public function category()
    {
        return $this->belongsTo(bidding_categorie::class, 'bidding_category_id', 'id')->select(['id', 'name']);
    }
}
