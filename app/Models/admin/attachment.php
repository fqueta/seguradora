<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attachment extends Model
{
    use HasFactory;
    protected $casts = [
        'file_config' => 'array',
    ];
    protected $fillable = [
        'title',
        'bidding_id',
        'file_file_name',
        'file_file_size',
        'file_content_type',
        'file_config',
        'order',
    ];
    public function bidding()
    {
  	    return $this->belongsTo(bidding_genres::class, 'bidding_id', 'id')->select(['id', 'name']);
    }
    // public function file_path()
    // {
  	//     // return $this->belongsTo('App\Models\admin\Bidding');
    //    return $this->hasMany(meta_attachment::class,'attachment_id','id')->select(['id', 'meta_key', 'meta_value']);
    //     // return $this->belongsTo(meta_attachment::class, 'attachment_id', 'id')->select(['id', 'meta_key', 'meta_value']);
    // }
}
