<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una versión del contrato, de los términos y condiciones o de la política de
 * privacidad.
 *
 * Una versión que alguien ya firmó NO se vuelve a tocar: corregirla cambiaría
 * lo que esa persona aceptó sin que lo sepa. Al editar un texto ya firmado se
 * crea la versión siguiente (ver App\Support\TextosLegales::guardar).
 */
class TextoLegal extends Model
{
    protected $table = 'textos_legales';

    protected $fillable = ['tipo', 'version', 'contenido', 'id_usuario'];

    protected $casts = [
        'version' => 'integer',
        'id_usuario' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
