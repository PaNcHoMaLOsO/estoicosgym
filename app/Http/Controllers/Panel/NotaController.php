<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Nota;
use Illuminate\Http\Request;

/**
 * El bloc de notas del mesón.
 *
 * Se escribe y se tacha desde la portada del panel, sin salir de ella: apuntar
 * «llamar a Juan» no puede costar tres clics y una pantalla nueva, o se acaba
 * apuntando en un papel al lado del teclado.
 */
class NotaController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'texto' => 'required|string|max:280',
        ], [
            'texto.required' => 'Escribe algo.',
            'texto.max' => 'Una nota son 280 caracteres. Para más, usa las observaciones de la ficha.',
        ]);

        Nota::create([
            'texto' => trim($datos['texto']),
            'id_usuario' => $request->user()->id,
        ]);

        // Sin aviso de exito: la nota aparece en la lista al instante y decir
        // ademas «nota creada» es ruido sobre algo que ya se esta viendo.
        return back();
    }

    /**
     * Tacha o destacha.
     *
     * Se puede destachar porque tacharla es un clic y equivocarse también: si
     * no se pudiera deshacer, habría que borrarla y escribirla otra vez.
     */
    public function alternar(Request $request, Nota $nota)
    {
        $hecha = ! $nota->hecha;

        $nota->update([
            'hecha' => $hecha,
            // Quién la tachó y cuándo. Al destacharla se borran los dos: dejar
            // el rastro de la vez anterior haría creer que sigue hecha.
            'hecha_en' => $hecha ? now() : null,
            'id_usuario_hecha' => $hecha ? $request->user()->id : null,
        ]);

        return back();
    }

    /**
     * La quita del bloc.
     *
     * Se borra de verdad y no va a la papelera: es un recordatorio, no un dato
     * del gimnasio, y llenar la papelera de «comprar café» la haría inútil para
     * lo que de verdad importa recuperar.
     */
    public function destroy(Nota $nota)
    {
        $nota->delete();

        return back();
    }
}
