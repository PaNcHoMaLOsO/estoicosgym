{{-- Aviso interno: alguien escribio desde el formulario del sitio publico.
     Los estilos van EN LINEA porque los clientes de correo no respetan hojas
     externas ni <style> en el <head>. --}}
<div style="font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; max-width: 520px; margin: 0 auto; color: #141417;">
    <div style="background: #0a0a0b; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
        <span style="font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">
            <span style="color: #d81f26;">PRO</span><span style="color: #ffffff;">GYM</span>
        </span>
    </div>

    <div style="background: #ffffff; padding: 24px; border: 1px solid #e1e1e4; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 4px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6a6a72;">
            Mensaje desde la web
        </p>
        <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600;">{{ $datos['nombre'] }}</p>

        {{-- El correo va arriba y como enlace: es lo primero que se necesita
             para responder, y asi se contesta con un clic. --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
            <tr>
                <td style="padding: 6px 0; color: #6a6a72; width: 90px;">Responder a</td>
                <td style="padding: 6px 0;">
                    <a href="mailto:{{ $datos['email'] }}" style="color: #d81f26; font-weight: 600;">
                        {{ $datos['email'] }}
                    </a>
                </td>
            </tr>
            @if (! empty($datos['telefono']))
                <tr>
                    <td style="padding: 6px 0; color: #6a6a72;">Teléfono</td>
                    <td style="padding: 6px 0;">{{ $datos['telefono'] }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #6a6a72;">Interés</td>
                <td style="padding: 6px 0;">{{ ucfirst($datos['servicio']) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #6a6a72;">Recibido</td>
                <td style="padding: 6px 0;">{{ $datos['fecha'] }}</td>
            </tr>
        </table>

        <p style="margin: 0 0 6px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #6a6a72;">
            Mensaje
        </p>
        <div style="background: #f6f6f7; border-left: 3px solid #d81f26; padding: 12px 14px; border-radius: 0 6px 6px 0; line-height: 1.5; white-space: pre-line;">{{ $datos['mensaje'] }}</div>

        <p style="margin: 20px 0 0; font-size: 12px; color: #6a6a72;">
            Enviado desde el formulario de contacto de la web.
        </p>
    </div>
</div>
