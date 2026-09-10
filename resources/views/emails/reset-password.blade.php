{{-- Correo de recuperacion de contraseña. Se arma con CorreoService y se manda
     por SMTP o Resend; los estilos van EN LINEA porque los clientes de correo
     no respetan hojas externas ni <style> en el <head>. --}}
<div style="font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; max-width: 480px; margin: 0 auto; color: #141417;">
    <div style="background: #0a0a0b; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
        <span style="font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">
            <span style="color: #d81f26;">PRO</span><span style="color: #ffffff;">GYM</span>
        </span>
    </div>

    <div style="background: #ffffff; padding: 24px; border: 1px solid #e1e1e4; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 12px;">Hola {{ $nombre }},</p>

        <p style="margin: 0 0 16px; line-height: 1.5; color: #3a3a40;">
            Recibimos una solicitud para restablecer la contraseña de tu cuenta. Pulsa el
            botón para elegir una nueva. El enlace vence en una hora.
        </p>

        <p style="text-align: center; margin: 24px 0;">
            <a href="{{ $enlace }}"
               style="display: inline-block; background: #d81f26; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600;">
                Restablecer contraseña
            </a>
        </p>

        <p style="margin: 0; line-height: 1.5; color: #6a6a72; font-size: 13px;">
            Si no fuiste tú, ignora este correo: tu contraseña seguirá igual.
        </p>
    </div>

    <p style="text-align: center; margin: 16px 0 0; color: #a0a0a9; font-size: 12px;">
        PRO GYM · Profesionales del deporte
    </p>
</div>
