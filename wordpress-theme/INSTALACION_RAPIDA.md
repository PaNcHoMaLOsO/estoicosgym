# 🚀 Instalación Rápida - WordPress + Tema EstoicosGym

## Opción A: Usando XAMPP (Windows) - 15 minutos

### Paso 1: Instalar XAMPP
1. Descarga XAMPP: https://www.apachefriends.org/download.html
2. Ejecuta el instalador (siguiente, siguiente, instalar)
3. Instala en `C:\xampp`

### Paso 2: Iniciar Servicios
1. Abre **XAMPP Control Panel**
2. Click en **Start** en Apache
3. Click en **Start** en MySQL

### Paso 3: Crear Base de Datos
1. Abre navegador: http://localhost/phpmyadmin
2. Click en **Nueva** (izquierda)
3. Nombre: `wordpress_estoicos`
4. Click **Crear**

### Paso 4: Instalar WordPress
1. Descarga WordPress: https://wordpress.org/latest.zip
2. Extrae el ZIP
3. Copia la carpeta `wordpress` a `C:\xampp\htdocs\`
4. Renombra a `estoicos` → `C:\xampp\htdocs\estoicos\`

### Paso 5: Configurar WordPress
1. Abre: http://localhost/estoicos
2. Idioma: **Español**
3. Configura BD:
   - Nombre BD: `wordpress_estoicos`
   - Usuario: `root`
   - Contraseña: *(dejar vacío)*
   - Servidor: `localhost`
4. Crea usuario admin de WordPress

### Paso 6: Instalar Tema EstoicosGym
1. Comprime la carpeta `estoicosgym` (dentro de wordpress-theme) en ZIP
2. En WordPress: **Apariencia > Temas > Añadir nuevo > Subir tema**
3. Sube el archivo `estoicosgym.zip`
4. Click **Activar**

### Paso 7: Ver el Tema
- Visita: http://localhost/estoicos

---

## Opción B: Usando LocalWP (Más Fácil Aún)

### Paso 1: Instalar LocalWP
1. Descarga: https://localwp.com/
2. Instala el programa

### Paso 2: Crear Sitio
1. Abre Local
2. Click **+ Create a new site**
3. Nombre: `estoicosgym`
4. Siguiente, siguiente, crear

### Paso 3: Instalar Tema
1. Click derecho en el sitio > **Go to site folder**
2. Navega a `app/public/wp-content/themes/`
3. Copia la carpeta `estoicosgym` ahí
4. En WordPress Admin: **Apariencia > Temas > Activar**

---

## 📁 Ubicación del Tema

El tema está en:
```
c:\GitHubDesk\estoicosgym\wordpress-theme\estoicosgym\
```

Para crear el ZIP:
1. Navega a `c:\GitHubDesk\estoicosgym\wordpress-theme\`
2. Click derecho en carpeta `estoicosgym`
3. **Enviar a > Carpeta comprimida (ZIP)**

---

## ✅ Verificación

Después de activar el tema, deberías ver:
- Header con logo "ESTOICOSGYM" y menú
- Hero section con "Forja tu mejor versión"
- Secciones: Servicios, Membresías, Horarios, Galería, Testimonios, Contacto
- Footer con información

## 🎨 Personalizar

Ve a **Apariencia > Personalizar** para cambiar:
- Logo
- Teléfono, email, dirección
- Redes sociales
- Textos del Hero
- URL del panel admin Laravel
