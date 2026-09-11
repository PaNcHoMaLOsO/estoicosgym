<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los dos correos del contrato por firmar: el que lleva el enlace y la copia
 * que le vuelve a quien firmó.
 *
 * Se editan en Plantillas de correo como los demás. No se ofrecen para mandar
 * a mano: el enlace se crea al enviar desde la ficha del socio, y sin él el
 * correo no sirve de nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $plantillas = [
            [
                'codigo' => 'contrato_para_firmar',
                'nombre' => 'Contrato para firmar',
                'descripcion' => 'Se manda desde la ficha del socio, o al darlo de alta. Lleva el enlace para leer y firmar el contrato.',
                'asunto_email' => '{firmante}, tu contrato con {gimnasio} está listo para firmar',
                'plantilla_email' => $this->cuerpo(<<<'HTML'
        <h2 style="color: #0a0a0b; margin: 0 0 14px 0; font-size: 21px;">Hola {firmante}</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 14px 0;">Para terminar la inscripción de <strong style="color: #0a0a0b;">{nombre}</strong> en {gimnasio} solo falta firmar el contrato.</p>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 22px 0;">Ábrelo en el celular o en el computador, léelo con calma y fírmalo con el dedo o con el mouse. Toma un par de minutos.</p>
        <div style="text-align: center; margin: 26px 0;">
            <a href="{enlace_contrato}" style="display: inline-block; background: #d81f26; color: #ffffff; padding: 15px 36px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">Leer y firmar el contrato</a>
        </div>
        <p style="color: #808080; font-size: 13px; line-height: 1.5; margin: 0 0 10px 0;">El enlace sirve hasta el <strong style="color: #0a0a0b;">{vence_enlace}</strong> y es solo para ti: no lo reenvíes.</p>
        <p style="color: #808080; font-size: 13px; line-height: 1.5; margin: 0;">Si el botón no funciona, copia esta dirección en tu navegador:<br><span style="color: #0a0a0b; word-break: break-all;">{enlace_contrato}</span></p>
HTML),
            ],
            [
                'codigo' => 'contrato_firmado',
                'nombre' => 'Contrato firmado',
                'descripcion' => 'Le llega a quien firmó, apenas firma: es su copia del contrato, completa.',
                'asunto_email' => 'Tu copia del contrato firmado con {gimnasio}',
                'plantilla_email' => $this->cuerpo(<<<'HTML'
        <h2 style="color: #0a0a0b; margin: 0 0 14px 0; font-size: 21px;">Hola {firmante}</h2>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 14px 0;">Quedó firmado el contrato de <strong style="color: #0a0a0b;">{nombre}</strong> con {gimnasio}, el {firmado_en}. Más abajo va tu copia completa.</p>
        <p style="color: #505050; font-size: 15px; line-height: 1.6; margin: 0 0 22px 0;">También puedes verla con la firma, imprimirla o guardarla como PDF desde aquí:</p>
        <div style="text-align: center; margin: 22px 0;">
            <a href="{enlace_contrato}" style="display: inline-block; background: #0a0a0b; color: #ffffff; padding: 13px 30px; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold;">Ver mi contrato firmado</a>
        </div>
HTML),
            ],
        ];

        foreach ($plantillas as $plantilla) {
            if (DB::table('tipo_notificaciones')->where('codigo', $plantilla['codigo'])->exists()) {
                continue;
            }

            DB::table('tipo_notificaciones')->insert($plantilla + [
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tipo_notificaciones')
            ->whereIn('codigo', ['contrato_para_firmar', 'contrato_firmado'])
            ->delete();
    }

    /** La cabecera y el pie de marca, los mismos de los demás correos. */
    private function cuerpo(string $medio): string
    {
        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <div style="background: #0a0a0b; color: #ffffff; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 42px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: Arial Black, Arial, sans-serif;"><span style="color: #d81f26;">PRO</span><span style="color: #FFFFFF;">GYM</span></h1>
    </div>
    <div style="padding: 28px 22px; background: #ffffff;">
{$medio}
    </div>
    <div style="background: #0a0a0b; color: #C7C7C7; padding: 22px 20px; text-align: center; font-size: 12px; line-height: 1.5;">
        {gimnasio} · Este correo lo mandó el gimnasio para el contrato de tu membresía.
    </div>
</div>
HTML;
    }
};
