<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefeituras extends Model
{
    use HasFactory;
    protected $table = 'tenants';
    protected $fillable = ['id','name','config','ativo','excluido','deletado','autor'];
}
