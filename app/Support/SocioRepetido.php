<?php

namespace App\Support;

use App\Models\Cliente;
use Illuminate\Support\Collection;

/**
 * ¿Esta persona ya está registrada?
 *
 * DUPLICAR UN SOCIO SALE CARO Y NO SE VE: su historial, sus pagos y su
 * contrato quedan repartidos en dos fichas, y nadie se entera hasta que algo
 * no cuadra. En el mesón pasa por tres caminos, y aquí se cierran los tres:
 *
 *  · El RUT escrito de otra forma. «21410708-2» y «21.410.708-2» son el mismo,
 *    y comparando el texto tal cual el sistema los daba por distintos.
 *  · El socio que no aparece. Uno de baja o en la papelera no salía en el
 *    aviso del alta, y el formulario terminaba rechazando el RUT con un
 *    mensaje que no decía de quién era ni qué hacer.
 *  · El que no trae RUT: un extranjero con pasaporte, o alguien que no se lo
 *    sabe. Ahí solo se puede sospechar —mismo celular, mismo nombre— y se
 *    avisa sin bloquear: dos hermanos pueden compartir teléfono.
 */
class SocioRepetido
{
    /** Membresía activa o pausada: tiene algo vigente. */
    private const VIGENTES = [100, 101];

    /**
     * El RUT como se guarda: «21.410.708-2», con la K en mayúscula.
     *
     * Si no parece un RUT —un pasaporte, algo a medio escribir— se devuelve tal
     * cual: no es cosa de esto decidir si es válido, eso lo dice la regla.
     */
    public static function rut(?string $texto): ?string
    {
        $limpio = BusquedaDeSocio::soloDigitos((string) $texto);

        if (strlen($limpio) < 2 || ! ctype_digit(substr($limpio, 0, -1))) {
            return $texto === null ? null : trim($texto);
        }

        $cuerpo = substr($limpio, 0, -1);
        $dv = substr($limpio, -1);

        return number_format((int) $cuerpo, 0, '', '.').'-'.$dv;
    }

    /** El socio con ese RUT, escrito como sea, INCLUIDOS los de la papelera. */
    public static function porRut(?string $rut, ?int $ignorar = null): ?Cliente
    {
        $limpio = BusquedaDeSocio::soloDigitos((string) $rut);

        if (strlen($limpio) < 3) {
            return null;
        }

        return Cliente::withTrashed()
            ->whereRaw("REPLACE(REPLACE(UPPER(run_pasaporte), '.', ''), '-', '') = ?", [$limpio])
            ->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))
            ->first();
    }

    /**
     * Los que se le parecen sin RUT de por medio: mismo celular, o mismo
     * nombre y apellido. Es una sospecha, no un bloqueo.
     *
     * @return Collection<int,Cliente>
     */
    public static function parecidos(?string $celular, ?string $nombres, ?string $apellido, ?int $ignorar = null): Collection
    {
        // Los ocho últimos dígitos: «+56 9 1234 5678» y «912345678» son el
        // mismo número, y el prefijo solo no dice nada.
        $numero = substr(preg_replace('/\D/', '', (string) $celular), -8);
        $nombre = mb_strtolower(trim((string) $nombres));
        $primerNombre = $nombre === '' ? '' : explode(' ', $nombre)[0];
        $apellido = mb_strtolower(trim((string) $apellido));

        $porCelular = strlen($numero) === 8;
        $porNombre = $primerNombre !== '' && $apellido !== '';

        if (! $porCelular && ! $porNombre) {
            return collect();
        }

        return Cliente::withTrashed()
            ->where(function ($q) use ($porCelular, $numero, $porNombre, $primerNombre, $apellido) {
                if ($porCelular) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(celular, ' ', ''), '+', ''), '-', '') LIKE ?", ["%{$numero}"]);
                }

                if ($porNombre) {
                    $q->orWhere(fn ($q) => $q
                        ->whereRaw('LOWER(nombres) LIKE ?', ["{$primerNombre}%"])
                        ->whereRaw('LOWER(apellido_paterno) = ?', [$apellido]));
                }
            })
            ->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))
            ->limit(5)
            ->get();
    }

    /**
     * Cómo se le cuenta al mesón quién es y en qué está, con lo que se puede
     * hacer: no es lo mismo alguien al día que alguien en la papelera.
     *
     * @return array<string,mixed>
     */
    public static function comoSeLee(Cliente $cliente, ?string $porque = null): array
    {
        $vigente = $cliente->inscripciones()
            ->with('membresia:id,nombre')
            ->whereIn('id_estado', self::VIGENTES)
            ->orderByDesc('fecha_vencimiento')
            ->first();

        $estado = match (true) {
            $cliente->datos_borrados_en !== null => 'datos_borrados',
            $cliente->trashed() => 'papelera',
            $vigente !== null => 'vigente',
            ! $cliente->activo => 'baja',
            default => 'sin_plan',
        };

        return [
            'id' => $cliente->id,
            'uuid' => $cliente->uuid,
            'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
            'rut' => $cliente->run_pasaporte,
            'celular' => $cliente->celular,
            'estado' => $estado,
            'plan' => $vigente?->membresia?->nombre,
            'vence' => $vigente?->fecha_vencimiento?->format('d/m/Y'),
            // Por qué se sospecha: «mismo celular», «mismo nombre».
            'porque' => $porque,
        ];
    }

    /** Lo que se le dice a quien intenta crearlo otra vez. */
    public static function mensaje(Cliente $cliente): string
    {
        $nombre = trim("{$cliente->nombres} {$cliente->apellido_paterno}");

        return match (self::comoSeLee($cliente)['estado']) {
            'papelera' => "Ese RUT es de {$nombre}, que está en la papelera. Restáurala desde Configuración → Papelera en vez de crear otra ficha.",
            'datos_borrados' => 'Ese RUT es de un socio al que se le borraron los datos personales. No se puede volver a usar.',
            'baja' => "Ese RUT ya es de {$nombre}, que está dado de baja. Búscalo y véndele un plan: se reactiva solo.",
            default => "Ese RUT ya es de {$nombre}. Búscalo en el buscador en vez de crear otra ficha.",
        };
    }
}
