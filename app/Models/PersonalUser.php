<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PersonalUser extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['id', 'name'];

    public $timestamps = false;

    protected $primaryKey = 'id'; // ✅ ISSO É ESSENCIAL

    public $incrementing = false; // ✅ Se a chave não for auto-incremental

    protected $keyType = 'int'; // ✅ Ou 'string' dependendo do tipo
}
