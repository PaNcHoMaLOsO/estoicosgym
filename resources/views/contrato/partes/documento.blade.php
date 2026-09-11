{{--
    El documento TAL COMO SE FIRMA.

    Se guarda entero —con la firma dibujada dentro— y su huella lo cubre. Por
    eso lleva sus propios estilos, escritos aquí, y no depende de la hoja de la
    web: esa puede cambiar mañana sin que cambie lo que se firmó.
--}}
<div class="cf">
<style>
.cf{font-family:Georgia,'Times New Roman',serif;color:#16161a;font-size:15px;line-height:1.6}
.cf h1{font-family:Arial,Helvetica,sans-serif;font-size:21px;font-weight:700;line-height:1.25;text-transform:uppercase;letter-spacing:.03em;margin:0 0 14px}
.cf h2{font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;margin:22px 0 6px}
.cf h3{font-size:15px;font-weight:700;margin:16px 0 4px}
.cf strong,.cf b{font-weight:700}
.cf p{margin:0 0 10px}
.cf ul,.cf ol{margin:0 0 12px;padding-left:22px}
.cf ul{list-style:disc}
.cf ol{list-style:decimal}
.cf li{margin:3px 0}
.cf a{color:#b81e25}
.cf-seccion{border-top:1px solid #d9d9de;margin-top:28px;padding-top:18px}
.cf-rotulo{font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#6a6a72;margin:0 0 10px}
.cf table{border-collapse:collapse;width:100%;font-family:Arial,Helvetica,sans-serif;font-size:13px;margin-top:8px}
.cf td{padding:6px 10px 6px 0;vertical-align:top;border-bottom:1px solid #ececf0}
.cf td:first-child{color:#6a6a72;width:38%}
.cf-trazo{display:block;width:100%;max-width:340px;height:auto;margin:16px 0 4px;border-bottom:1px solid #16161a}
.cf-pie{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6a6a72;margin:0}
</style>

<div class="cf-texto">
{!! $contrato_html !!}
</div>

<div class="cf-seccion">
    <p class="cf-rotulo">Anexo · Términos y condiciones · versión {{ $textos['terminos']->version }}</p>
    {!! $terminos_html !!}
</div>

<div class="cf-seccion">
    <p class="cf-rotulo">Aceptación y firma</p>
    <ul>
        <li>Aceptó este contrato (versión {{ $textos['contrato']->version }}) y los términos y condiciones (versión {{ $textos['terminos']->version }}).</li>
        <li>Declaró haber leído la política de privacidad (versión {{ $textos['privacidad']->version }}), publicada en {{ $enlace_privacidad }}.</li>
        <li>Foto en su ficha: {{ $consentimiento_imagen ? 'autorizada' : 'no autorizada' }}.</li>
        <li>Su imagen en las redes sociales del gimnasio: {{ $consentimiento_difusion ? 'autorizada' : 'no autorizada' }}.</li>
    </ul>

    <table>
        <tr><td>Firma</td><td>{{ $nombre }}</td></tr>
        <tr><td>RUT o pasaporte</td><td>{{ $rut }}</td></tr>
        <tr><td>En calidad de</td><td>{{ $firmante['tipo'] === 'apoderado' ? 'Apoderado de ' . $socio : 'Socio' }}</td></tr>
        <tr><td>Fecha y hora</td><td>{{ $fecha->format('d/m/Y H:i:s') }} (hora de Chile)</td></tr>
        <tr><td>Enlace enviado a</td><td>{{ $email ?? '—' }}</td></tr>
        <tr><td>Conexión</td><td>{{ $ip ?? '—' }}</td></tr>
    </table>

    <img class="cf-trazo" src="{{ $firma }}" alt="Firma de {{ $nombre }}">
    <p class="cf-pie">{{ $nombre }} · {{ $gimnasio['gimnasio'] }}</p>
</div>
</div>
