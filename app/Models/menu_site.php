<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class menu_site extends Model
{
    use HasFactory,Notifiable;
    protected $fillable = [
        'categoria',
        'description',
        'url',
        'pai',
        'route',
        'premission',
        'page_id',
        'icon',
        'actived',
    ];
}
