<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\ChatDeWhatsapp;
use Inertia\Inertia;

/**
 * El chat de WhatsApp a pantalla completa.
 *
 * Las conversaciones salen de App\Support\ChatDeWhatsapp, la misma maqueta que
 * se ve en la columna lateral del resumen: dos copias acabarían enseñando
 * cosas distintas.
 */
class WhatsappController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Whatsapp', [
            'conversaciones' => ChatDeWhatsapp::conversaciones(),
            'plantillas' => ChatDeWhatsapp::plantillas(),
            'esMaqueta' => true,
        ]);
    }
}
