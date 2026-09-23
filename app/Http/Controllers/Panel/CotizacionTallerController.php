<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CotizacionTaller;
use App\Models\Taller;
use App\Support\Ajustes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Las cotizaciones de un taller: el papel con el que empieza el mes.
 *
 * EL COLEGIO PREGUNTA CUÁNTO SALE ABRIL y hay que mandarle una cotización con
 * las horas y el total. Se hacía copiando el Word del mes anterior y cambiando
 * a mano el número, las fechas y la cifra, después de contar las clases con el
 * calendario de Windows al lado. Aquí el horario propone las clases, se
 * destildan las que no van a haber y la cuenta se rehace sola.
 *
 * Y SE PUEDE CORREGIR DESPUÉS, que es lo que de verdad pasa: el colegio
 * suspende una semana, cae un feriado, se corre un horario. Quitar dos clases y
 * volver a imprimirla son dos clics, no rehacer el documento.
 */
class CotizacionTallerController extends Controller
{
    /** Cotiza un mes: trae sus clases del horario y deja el borrador hecho. */
    public function crear(Request $request, Taller $taller)
    {
        $datos = $request->validate([
            'periodo' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $mes = $this->mes($datos['periodo'] ?? null);
        $hoy = Carbon::today();

        $cotizacion = new CotizacionTaller([
            'id_taller' => $taller->id,
            'numero' => CotizacionTaller::siguienteNumero(),
            'periodo' => $mes->format('Y-m'),
            'fecha' => $hoy,
            // Un mes de validez: es lo que han dicho siempre las del gimnasio.
            'valido_hasta' => $hoy->copy()->addMonth(),
            'descripcion' => $taller->descripcion_factura ?: $taller->nombre,
            'precio_hora' => $taller->precio_hora,
            'estado' => 'borrador',
            'id_usuario' => $request->user()->id,
        ]);

        $cotizacion->rehacerLaCuenta(CotizacionTaller::clasesParaCotizar($taller, $mes));
        $cotizacion->save();

        return redirect()
            ->route('panel.talleres.cotizaciones.show', $cotizacion->uuid)
            ->with('success', 'Cotización N° '.$cotizacion->numero.' preparada. Quita las clases que no va a haber.');
    }

    /** La cotización para revisarla, corregirla y mandarla. */
    public function show(CotizacionTaller $cotizacion)
    {
        $cotizacion->load('taller.institucion');

        return Inertia::render('Talleres/Cotizacion', [
            'cotizacion' => $this->comoSeLee($cotizacion),
            'taller' => [
                'uuid' => $cotizacion->taller->uuid,
                'nombre' => $cotizacion->taller->nombre,
                'precio_hora' => $cotizacion->taller->precio_hora,
                'institucion' => $cotizacion->taller->institucion?->only([
                    'nombre', 'rut', 'giro', 'direccion', 'comuna',
                    'contacto_nombre', 'contacto_email', 'contacto_telefono',
                ]),
            ],
            'estados' => CotizacionTaller::ESTADOS,
        ]);
    }

    /**
     * Guarda la cotización corregida.
     *
     * LAS LÍNEAS VIENEN ENTERAS, incluidas las destildadas: una cotización que
     * dice «el 1 de mayo no hay clase» explica por qué el mes sale más barato,
     * y esa es la mitad de la conversación con el colegio.
     */
    public function actualizar(Request $request, CotizacionTaller $cotizacion)
    {
        $datos = $request->validate([
            'numero' => [
                'required', 'integer', 'min:1', 'max:999999',
                // El número va en el papel: repetido, dos cotizaciones
                // distintas se llaman igual y no hay manera de saber cuál
                // aceptó el colegio.
                Rule::unique('cotizaciones_taller', 'numero')->ignore($cotizacion->id)->whereNull('deleted_at'),
            ],
            'fecha' => 'required|date',
            'valido_hasta' => 'required|date|after_or_equal:fecha',
            'descripcion' => 'required|string|max:200',
            'precio_hora' => 'required|integer|min:1|max:9999999',
            'estado' => ['required', Rule::in(array_keys(CotizacionTaller::ESTADOS))],
            'notas' => 'nullable|string|max:1000',
            'detalle' => 'array',
            'detalle.*.fecha' => 'nullable|date',
            'detalle.*.detalle' => 'nullable|string|max:160',
            'detalle.*.horas' => 'required|numeric|min:0|max:24',
            'detalle.*.incluida' => 'boolean',
        ], [
            'numero.unique' => 'Ya hay otra cotización con ese número.',
            'valido_hasta.after_or_equal' => 'La cotización no puede vencer antes de escribirse.',
        ]);

        $cotizacion->fill([
            'numero' => $datos['numero'],
            'fecha' => $datos['fecha'],
            'valido_hasta' => $datos['valido_hasta'],
            'descripcion' => trim($datos['descripcion']),
            'precio_hora' => $datos['precio_hora'],
            'estado' => $datos['estado'],
            'notas' => $datos['notas'] ?? null,
        ]);

        $cotizacion->rehacerLaCuenta($datos['detalle'] ?? []);
        $cotizacion->save();

        return back()->with('success', 'Cotización guardada.');
    }

    /**
     * Vuelve a traer las clases del horario para el mes cotizado.
     *
     * HACE FALTA CUANDO CAMBIA EL HORARIO: el colegio corre las clases de los
     * viernes y la cotización de mayo quedó con las de antes. Lo ya destildado
     * se respeta —si se quitó el 1 de mayo, sigue quitado—, y lo escrito a mano
     * no se toca: solo se añade lo que falta.
     */
    public function refrescar(CotizacionTaller $cotizacion)
    {
        if (! $cotizacion->periodo) {
            return back()->with('error', 'Esta cotización no es de un mes concreto.');
        }

        $mes = $this->mes($cotizacion->periodo);
        $lineas = $cotizacion->detalle ?? [];
        $puestas = array_map(fn (array $l) => ($l['fecha'] ?? '').'|'.($l['detalle'] ?? ''), $lineas);
        $nuevas = 0;

        foreach (CotizacionTaller::clasesParaCotizar($cotizacion->taller, $mes) as $clase) {
            if (in_array($clase['fecha'].'|'.$clase['detalle'], $puestas, true)) {
                continue;
            }

            $lineas[] = $clase;
            $nuevas++;
        }

        $cotizacion->rehacerLaCuenta($lineas);
        $cotizacion->save();

        return back()->with(
            $nuevas > 0 ? 'success' : 'info',
            $nuevas > 0
                ? $nuevas.' clases del horario añadidas.'
                : 'El horario no propone ninguna clase que no esté ya.'
        );
    }

    /** A la papelera del olvido: un borrador que no se mandó no estorba. */
    public function eliminar(CotizacionTaller $cotizacion)
    {
        $taller = $cotizacion->taller;
        $cotizacion->delete();

        return redirect()
            ->route('panel.talleres.show', $taller->uuid)
            ->with('success', 'Cotización N° '.$cotizacion->numero.' eliminada.');
    }

    /**
     * La cotización en papel: se imprime o se guarda como PDF para mandarla.
     *
     * SALE DEL NAVEGADOR y no de un generador de PDF: el documento es una hoja
     * con seis datos, y «Guardar como PDF» del navegador la deja igual que el
     * Word que se mandaba antes sin meter una librería más que mantener.
     */
    public function imprimir(Request $request, CotizacionTaller $cotizacion)
    {
        $cotizacion->load('taller.institucion');

        return view('talleres.cotizacion', [
            'cotizacion' => $cotizacion,
            'taller' => $cotizacion->taller,
            'institucion' => $cotizacion->taller->institucion,
            'emisor' => $this->emisor(),
            // El detalle de las horas se puede dejar fuera: son dos hojas más
            // y hay veces —el colegio ya sabe el horario— en que sobra.
            'conDetalle' => $request->query('detalle') !== 'no',
        ]);
    }

    // ------------------------------------------------------------ apoyo

    /**
     * Quién cotiza: los datos del gimnasio, tal como salen en el papel.
     *
     * @return array<string,string>
     */
    private function emisor(): array
    {
        return [
            'nombre' => Ajustes::obtener('gimnasio.razon_social') ?: Ajustes::obtener('gimnasio.nombre'),
            'rut' => (string) Ajustes::obtener('gimnasio.rut'),
            'direccion' => trim(implode('; ', array_filter([
                Ajustes::obtener('gimnasio.direccion'),
                Ajustes::obtener('gimnasio.comuna'),
            ]))),
            'telefono' => (string) Ajustes::obtener('gimnasio.telefono'),
            'email' => (string) Ajustes::obtener('gimnasio.email'),
        ];
    }

    private function mes(?string $periodo): Carbon
    {
        if ($periodo && preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            return Carbon::createFromFormat('Y-m-d', $periodo.'-01')->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    /** @return array<string,mixed> */
    private function comoSeLee(CotizacionTaller $cotizacion): array
    {
        return [
            'uuid' => $cotizacion->uuid,
            'numero' => $cotizacion->numero,
            'periodo' => $cotizacion->periodo,
            'mes' => $cotizacion->periodo
                ? Carbon::createFromFormat('Y-m-d', $cotizacion->periodo.'-01')->translatedFormat('F \d\e Y')
                : null,
            'fecha' => $cotizacion->fecha->format('Y-m-d'),
            'valido_hasta' => $cotizacion->valido_hasta->format('Y-m-d'),
            'vencida' => $cotizacion->estaVencida(),
            'descripcion' => $cotizacion->descripcion,
            'precio_hora' => $cotizacion->precio_hora,
            'horas' => $cotizacion->horas,
            'total' => $cotizacion->total,
            'neto' => $cotizacion->neto,
            'iva' => $cotizacion->iva,
            'estado' => $cotizacion->estado,
            'notas' => $cotizacion->notas,
            'detalle' => array_map(fn (array $l) => [
                'fecha' => $l['fecha'] ?? null,
                'dia' => isset($l['fecha']) && $l['fecha']
                    ? Carbon::parse($l['fecha'])->translatedFormat('D')
                    : null,
                'detalle' => $l['detalle'] ?? null,
                'horas' => (float) ($l['horas'] ?? 0),
                'incluida' => (bool) ($l['incluida'] ?? true),
            ], $cotizacion->detalle ?? []),
        ];
    }
}
