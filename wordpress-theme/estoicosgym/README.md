# EstoicosGym - WordPress Theme

Tema WordPress profesional para la página de aterrizaje del gimnasio EstoicosGym.

## 📋 Características

- ✅ Diseño moderno y responsive
- ✅ Colores personalizados (paleta Estoicos)
- ✅ Secciones: Hero, Servicios, Membresías, Horarios, Galería, Testimonios, Contacto
- ✅ Formulario de contacto con AJAX
- ✅ Custom Post Types para Membresías, Testimonios y Galería
- ✅ Panel de personalización (Customizer)
- ✅ Acceso admin oculto para el panel Laravel
- ✅ Optimizado para SEO

## 🚀 Instalación en BanaHosting cPanel

### Paso 1: Subir Archivos WordPress

1. Accede a tu cPanel de BanaHosting
2. Ve a **Administrador de Archivos**
3. Navega a `public_html` (o subdirectorio si usarás subdirectorio)
4. Descarga WordPress desde https://wordpress.org/download/
5. Sube y extrae el archivo ZIP de WordPress

### Paso 2: Crear Base de Datos

1. En cPanel, ve a **Bases de Datos MySQL**
2. Crea una nueva base de datos: `estoicos_wp`
3. Crea un usuario: `estoicos_user`
4. Asigna el usuario a la base de datos con todos los privilegios
5. Anota las credenciales

### Paso 3: Instalar WordPress

1. Visita tu dominio en el navegador
2. Completa la instalación de WordPress:
   - Base de datos: `prefijo_estoicos_wp`
   - Usuario: `prefijo_estoicos_user`
   - Contraseña: la que creaste
   - Host: `localhost`
   - Prefijo: `wp_`

### Paso 4: Instalar el Tema EstoicosGym

1. Comprime la carpeta `estoicosgym` en un archivo ZIP
2. En WordPress Admin, ve a **Apariencia > Temas**
3. Click en **Añadir nuevo > Subir tema**
4. Sube el archivo `estoicosgym.zip`
5. **Activa** el tema

### Paso 5: Configurar el Tema

1. Ve a **Apariencia > Personalizar**
2. Configura:
   - **Información del Gimnasio**: Teléfono, Email, Dirección
   - **Redes Sociales**: Instagram, Facebook, WhatsApp
   - **Sección Hero**: Título, subtítulo, imagen
   - **Acceso Administrador**: URL del panel Laravel

### Paso 6: Configurar Página de Inicio

1. Ve a **Ajustes > Lectura**
2. Selecciona "Una página estática"
3. Página de inicio: Crea una página vacía llamada "Inicio"
4. Guarda cambios

### Paso 7: Crear Página de Acceso Admin (Oculta)

1. Ve a **Páginas > Añadir nueva**
2. Título: "Admin Login" (o cualquier nombre)
3. URL slug: `admin-login` (esto creará tudominio.com/admin-login)
4. En **Atributos de página > Plantilla**: Selecciona "Admin Login"
5. Publica la página (no aparecerá en menús)

## 🔧 Configuración del Acceso Admin Oculto

El acceso oculto al panel de administración Laravel funciona así:

1. Ve a **Apariencia > Personalizar > Acceso Administrador**
2. Configura la URL del panel Laravel:
   - Si Laravel está en subdirectorio: `/sistema-admin/login`
   - Si Laravel está en subdominio: `https://admin.tudominio.com/login`
3. Marca/desmarca "Mostrar botón de acceso admin" según preferencia

### URLs de Acceso

- **Página pública**: `https://tudominio.com`
- **Admin oculto**: `https://tudominio.com/admin-login` → Redirige a Laravel
- **WordPress Admin**: `https://tudominio.com/wp-admin`

## 📁 Estructura del Tema

```
estoicosgym/
├── assets/
│   ├── js/
│   │   └── main.js
│   └── images/
├── style.css           # Estilos principales
├── functions.php       # Funciones del tema
├── header.php          # Cabecera
├── footer.php          # Pie de página
├── index.php           # Plantilla principal
├── front-page.php      # Página de inicio
├── page.php            # Páginas estáticas
├── single.php          # Posts individuales
├── 404.php             # Página de error
├── page-admin-login.php # Redirección admin
└── README.md           # Este archivo
```

## 🎨 Personalización de Colores

Los colores se definen en `style.css`:

```css
:root {
    --primary-color: #1a1a2e;
    --primary-dark: #0f0f1a;
    --accent-color: #e94560;
    --success-color: #00bf8e;
    --white: #ffffff;
    --text-dark: #2d2d2d;
    --text-muted: #a0a0a0;
}
```

## 📝 Custom Post Types

### Membresías
- Precio
- Período (mensual, trimestral, etc.)
- Destacada (sí/no)
- Características (lista)

### Testimonios
- Contenido del testimonio
- Nombre del cliente
- Cargo/Descripción
- Foto

### Galería
- Imagen destacada
- Título

## 🔒 Seguridad

1. **Ocultar wp-admin**: El acceso al admin de WordPress permanece en `/wp-admin`
2. **Acceso Laravel oculto**: Solo accesible vía `/admin-login`
3. **No mostrar en menús**: La página de redirección no se incluye en navegación

## 📱 Responsive

El tema es completamente responsive con breakpoints:
- Desktop: > 1024px
- Tablet: 768px - 1024px
- Mobile: < 768px

## 🆘 Soporte

Para soporte técnico, contacta a: contacto@estoicosgym.cl
