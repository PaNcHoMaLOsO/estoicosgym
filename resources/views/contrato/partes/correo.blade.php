{{--
    La copia que va dentro del correo: el mismo texto, con los estilos escritos
    en cada etiqueta, porque los correos no leen hojas de estilo.

    La firma dibujada no va: casi ningún correo muestra imágenes incrustadas.
    Está en el enlace, junto con todo lo demás.
--}}
<div style="max-width: 600px; margin: 18px auto 0; padding: 26px 22px; background: #ffffff; border-top: 4px solid #d81f26; font-family: Georgia, 'Times New Roman', serif; color: #1a1a1a; font-size: 14px; line-height: 1.6;">
    {!! $contrato_html !!}

    <hr style="border: 0; border-top: 1px solid #e2e2e6; margin: 24px 0;">

    <p style="font-family: Arial, sans-serif; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: #6a6a72; margin: 0 0 8px;">Anexo · Términos y condiciones · versión {{ $textos['terminos']->version }}</p>
    {!! $terminos_html !!}

    <hr style="border: 0; border-top: 1px solid #e2e2e6; margin: 24px 0;">

    <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px;">
        <tr>
            <td style="padding: 4px 0; color: #6a6a72; width: 42%;">Firmado por</td>
            <td style="padding: 4px 0;">{{ $contrato->firmante_nombre }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6a6a72;">RUT o pasaporte</td>
            <td style="padding: 4px 0;">{{ $contrato->firmante_rut }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6a6a72;">En calidad de</td>
            <td style="padding: 4px 0;">{{ $firmante['tipo'] === 'apoderado' ? 'Apoderado de ' . $socio : 'Socio' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6a6a72;">Fecha y hora</td>
            <td style="padding: 4px 0;">{{ $contrato->firmado_en->format('d/m/Y H:i') }} (hora de Chile)</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6a6a72;">Huella del documento</td>
            <td style="padding: 4px 0; font-family: monospace; font-size: 11px; word-break: break-all;">{{ $contrato->huella }}</td>
        </tr>
    </table>
</div>
