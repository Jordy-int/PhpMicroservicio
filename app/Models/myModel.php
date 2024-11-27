<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class myModel extends Model
{
    use HasFactory;

    protected $table = "User";

    protected $fillable = [
        "idUsuario",
        'nombreCompleto',
        'cedula',
        'numero',
        'correo',
        'contrasena',
        'departamento',
        'municipio',
        'direccion',
    ];
}
