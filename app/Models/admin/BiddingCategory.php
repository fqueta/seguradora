<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiddingCategory extends Model
{
    use HasFactory;
    protected $table = 'bidding_categories';
    protected $fillable = [
        'name',
        'ativo',
        'autor',
        'excluido',
        'deletado',
    ];
}
