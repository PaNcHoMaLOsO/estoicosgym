# Documentos históricos

Fotos del sistema en un momento concreto, casi todas del **8 de diciembre de
2025**. Se guardan porque varias son entregables de la evaluación —hablan de
requerimientos RF-01 a RF-07— y borrarlas perdería el registro de cómo se
presentó el prototipo.

**No describen el sistema de hoy.** Desde entonces cambiaron, entre otras cosas:

- Se montó un panel nuevo en Inertia + React bajo `/panel`, con la paleta del
  logotipo, y el de Blade (`/admin`) se cerró.
- La base pasó de MySQL a PostgreSQL (septiembre de 2026), que es lo que usa el
  servidor.
- Los permisos por rol pasaron de ser una columna decorativa a aplicarse de
  verdad en las 129 rutas del panel.
- Se corrigieron fallos que estos documentos daban por buenos: el abono que
  reventaba al guardarse, los pagos que se duplicaban con el doble clic, el
  segundo factor que se saltaba solo, el enlace de recuperación que no caducaba
  y los informes que ocultaban la mitad del dinero cobrado.
- El envío de correo admite dos vías (SMTP y Resend) con una de respaldo.

Para el estado actual: el [README](../../README.md) de la raíz y
[docs/](../README.md).

Aquí también quedaron, en septiembre de 2026, los documentos que estaban sueltos
en la raíz y en `docs/`: los de notificaciones, correo, colores de los correos,
la guía de código y las presentaciones de requerimientos. Describen el sistema
de antes.

## Qué hay aquí

| Documento | Qué era |
|---|---|
| `ESTADO_MODULOS.md` | Estado de cada módulo al 8-dic-2025 |
| `EVALUACION_RF_2_3_4_7.md` | Evaluación de los requerimientos 2, 3, 4 y 7 |
| `RESUMEN_EVALUACION.md` | Resumen ejecutivo de esa evaluación |
| `VERIFICACION_MODULOS_FINAL.md` | Verificación previa a la presentación |
| `DIAGNOSTICO_EMAILS.md` | Diagnóstico de por qué no salían los correos |
| `FIX_CODIFICACION_UTF8.md` | Notas del arreglo de acentos |
| `EMAILS_CONFIGURACION.md` | Configuración de correo de entonces (ver `docs/CONFIGURACION_EMAILS.md`) |
