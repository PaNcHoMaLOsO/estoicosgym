<?php
/**
 * EstoicosGym Theme Functions
 * 
 * @package EstoicosGym
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme Version
define('ESTOICOSGYM_VERSION', '1.0.0');
define('ESTOICOSGYM_DIR', get_template_directory());
define('ESTOICOSGYM_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function estoicosgym_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Menú Principal', 'estoicosgym'),
        'footer'  => __('Menú Footer', 'estoicosgym'),
    ));
    
    // Image sizes
    add_image_size('hero-image', 800, 800, true);
    add_image_size('service-icon', 200, 200, true);
    add_image_size('gallery-thumb', 400, 400, true);
    add_image_size('gallery-large', 800, 600, true);
}
add_action('after_setup_theme', 'estoicosgym_setup');

/**
 * Enqueue scripts and styles
 */
function estoicosgym_scripts() {
    // Google Fonts
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );
    
    // Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );
    
    // Main stylesheet
    wp_enqueue_style(
        'estoicosgym-style',
        get_stylesheet_uri(),
        array(),
        ESTOICOSGYM_VERSION
    );
    
    // Custom JS
    wp_enqueue_script(
        'estoicosgym-main',
        ESTOICOSGYM_URI . '/assets/js/main.js',
        array('jquery'),
        ESTOICOSGYM_VERSION,
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('estoicosgym-main', 'estoicosgym_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('estoicosgym_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'estoicosgym_scripts');

/**
 * Theme Customizer
 */
function estoicosgym_customize_register($wp_customize) {
    
    // ========================================
    // SECCIÓN: Información General
    // ========================================
    $wp_customize->add_section('estoicosgym_general', array(
        'title'    => __('Información del Gimnasio', 'estoicosgym'),
        'priority' => 30,
    ));
    
    // Teléfono
    $wp_customize->add_setting('gym_phone', array(
        'default'           => '+56 9 1234 5678',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('gym_phone', array(
        'label'   => __('Teléfono', 'estoicosgym'),
        'section' => 'estoicosgym_general',
        'type'    => 'text',
    ));
    
    // Email
    $wp_customize->add_setting('gym_email', array(
        'default'           => 'contacto@estoicosgym.cl',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('gym_email', array(
        'label'   => __('Email', 'estoicosgym'),
        'section' => 'estoicosgym_general',
        'type'    => 'email',
    ));
    
    // Dirección
    $wp_customize->add_setting('gym_address', array(
        'default'           => 'Av. Principal #123, Santiago',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('gym_address', array(
        'label'   => __('Dirección', 'estoicosgym'),
        'section' => 'estoicosgym_general',
        'type'    => 'text',
    ));
    
    // ========================================
    // SECCIÓN: Redes Sociales
    // ========================================
    $wp_customize->add_section('estoicosgym_social', array(
        'title'    => __('Redes Sociales', 'estoicosgym'),
        'priority' => 35,
    ));
    
    // Instagram
    $wp_customize->add_setting('social_instagram', array(
        'default'           => 'https://instagram.com/estoicosgym',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('social_instagram', array(
        'label'   => __('Instagram URL', 'estoicosgym'),
        'section' => 'estoicosgym_social',
        'type'    => 'url',
    ));
    
    // Facebook
    $wp_customize->add_setting('social_facebook', array(
        'default'           => 'https://facebook.com/estoicosgym',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('social_facebook', array(
        'label'   => __('Facebook URL', 'estoicosgym'),
        'section' => 'estoicosgym_social',
        'type'    => 'url',
    ));
    
    // WhatsApp
    $wp_customize->add_setting('social_whatsapp', array(
        'default'           => '+56912345678',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('social_whatsapp', array(
        'label'   => __('WhatsApp (número)', 'estoicosgym'),
        'section' => 'estoicosgym_social',
        'type'    => 'text',
    ));
    
    // ========================================
    // SECCIÓN: Hero
    // ========================================
    $wp_customize->add_section('estoicosgym_hero', array(
        'title'    => __('Sección Hero', 'estoicosgym'),
        'priority' => 40,
    ));
    
    // Título Hero
    $wp_customize->add_setting('hero_title', array(
        'default'           => 'Forja tu mejor versión',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title', array(
        'label'   => __('Título Principal', 'estoicosgym'),
        'section' => 'estoicosgym_hero',
        'type'    => 'text',
    ));
    
    // Subtítulo Hero
    $wp_customize->add_setting('hero_subtitle', array(
        'default'           => 'Entrena con disciplina estoica. Transforma tu cuerpo y mente con nuestros programas personalizados y equipamiento de última generación.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('hero_subtitle', array(
        'label'   => __('Subtítulo', 'estoicosgym'),
        'section' => 'estoicosgym_hero',
        'type'    => 'textarea',
    ));
    
    // Imagen Hero
    $wp_customize->add_setting('hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'hero_image', array(
        'label'     => __('Imagen Hero', 'estoicosgym'),
        'section'   => 'estoicosgym_hero',
        'mime_type' => 'image',
    )));
    
    // ========================================
    // SECCIÓN: Admin Access
    // ========================================
    $wp_customize->add_section('estoicosgym_admin', array(
        'title'    => __('Acceso Administrador', 'estoicosgym'),
        'priority' => 200,
    ));
    
    // URL del panel admin Laravel
    $wp_customize->add_setting('admin_panel_url', array(
        'default'           => '/sistema-admin',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('admin_panel_url', array(
        'label'       => __('URL Panel Administrador', 'estoicosgym'),
        'description' => __('URL oculta para acceder al sistema de gestión Laravel', 'estoicosgym'),
        'section'     => 'estoicosgym_admin',
        'type'        => 'text',
    ));
    
    // Mostrar botón admin
    $wp_customize->add_setting('show_admin_button', array(
        'default'           => false,
        'sanitize_callback' => 'estoicosgym_sanitize_checkbox',
    ));
    $wp_customize->add_control('show_admin_button', array(
        'label'   => __('Mostrar botón de acceso admin', 'estoicosgym'),
        'section' => 'estoicosgym_admin',
        'type'    => 'checkbox',
    ));
}
add_action('customize_register', 'estoicosgym_customize_register');

/**
 * Sanitize checkbox
 */
function estoicosgym_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Custom Post Types
 */
function estoicosgym_register_post_types() {
    
    // Membresías
    register_post_type('membresia', array(
        'labels' => array(
            'name'          => __('Membresías', 'estoicosgym'),
            'singular_name' => __('Membresía', 'estoicosgym'),
            'add_new'       => __('Agregar Membresía', 'estoicosgym'),
            'add_new_item'  => __('Agregar Nueva Membresía', 'estoicosgym'),
            'edit_item'     => __('Editar Membresía', 'estoicosgym'),
        ),
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-awards',
        'supports'     => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    ));
    
    // Testimonios
    register_post_type('testimonio', array(
        'labels' => array(
            'name'          => __('Testimonios', 'estoicosgym'),
            'singular_name' => __('Testimonio', 'estoicosgym'),
            'add_new'       => __('Agregar Testimonio', 'estoicosgym'),
            'add_new_item'  => __('Agregar Nuevo Testimonio', 'estoicosgym'),
            'edit_item'     => __('Editar Testimonio', 'estoicosgym'),
        ),
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-format-quote',
        'supports'     => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    ));
    
    // Galería
    register_post_type('galeria', array(
        'labels' => array(
            'name'          => __('Galería', 'estoicosgym'),
            'singular_name' => __('Imagen', 'estoicosgym'),
            'add_new'       => __('Agregar Imagen', 'estoicosgym'),
            'add_new_item'  => __('Agregar Nueva Imagen', 'estoicosgym'),
            'edit_item'     => __('Editar Imagen', 'estoicosgym'),
        ),
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-format-gallery',
        'supports'     => array('title', 'thumbnail'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'estoicosgym_register_post_types');

/**
 * Add meta boxes for custom post types
 */
function estoicosgym_add_meta_boxes() {
    // Meta box para Membresías
    add_meta_box(
        'membresia_details',
        __('Detalles de la Membresía', 'estoicosgym'),
        'estoicosgym_membresia_meta_box',
        'membresia',
        'normal',
        'high'
    );
    
    // Meta box para Testimonios
    add_meta_box(
        'testimonio_details',
        __('Información del Cliente', 'estoicosgym'),
        'estoicosgym_testimonio_meta_box',
        'testimonio',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'estoicosgym_add_meta_boxes');

/**
 * Membresía meta box callback
 */
function estoicosgym_membresia_meta_box($post) {
    wp_nonce_field('estoicosgym_membresia_nonce', 'membresia_nonce');
    
    $precio = get_post_meta($post->ID, '_membresia_precio', true);
    $periodo = get_post_meta($post->ID, '_membresia_periodo', true);
    $destacada = get_post_meta($post->ID, '_membresia_destacada', true);
    $caracteristicas = get_post_meta($post->ID, '_membresia_caracteristicas', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="membresia_precio">Precio ($)</label></th>
            <td><input type="number" id="membresia_precio" name="membresia_precio" value="<?php echo esc_attr($precio); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="membresia_periodo">Período</label></th>
            <td>
                <select id="membresia_periodo" name="membresia_periodo">
                    <option value="mes" <?php selected($periodo, 'mes'); ?>>Mensual</option>
                    <option value="trimestre" <?php selected($periodo, 'trimestre'); ?>>Trimestral</option>
                    <option value="semestre" <?php selected($periodo, 'semestre'); ?>>Semestral</option>
                    <option value="año" <?php selected($periodo, 'año'); ?>>Anual</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="membresia_destacada">¿Destacada?</label></th>
            <td><input type="checkbox" id="membresia_destacada" name="membresia_destacada" value="1" <?php checked($destacada, '1'); ?>></td>
        </tr>
        <tr>
            <th><label for="membresia_caracteristicas">Características (una por línea)</label></th>
            <td><textarea id="membresia_caracteristicas" name="membresia_caracteristicas" rows="6" class="large-text"><?php echo esc_textarea($caracteristicas); ?></textarea></td>
        </tr>
    </table>
    <?php
}

/**
 * Testimonio meta box callback
 */
function estoicosgym_testimonio_meta_box($post) {
    wp_nonce_field('estoicosgym_testimonio_nonce', 'testimonio_nonce');
    
    $cliente_nombre = get_post_meta($post->ID, '_testimonio_cliente', true);
    $cliente_cargo = get_post_meta($post->ID, '_testimonio_cargo', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="testimonio_cliente">Nombre del Cliente</label></th>
            <td><input type="text" id="testimonio_cliente" name="testimonio_cliente" value="<?php echo esc_attr($cliente_nombre); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="testimonio_cargo">Descripción/Cargo</label></th>
            <td><input type="text" id="testimonio_cargo" name="testimonio_cargo" value="<?php echo esc_attr($cliente_cargo); ?>" class="regular-text" placeholder="Ej: Miembro desde 2023"></td>
        </tr>
    </table>
    <?php
}

/**
 * Save meta boxes
 */
function estoicosgym_save_meta_boxes($post_id) {
    // Membresía
    if (isset($_POST['membresia_nonce']) && wp_verify_nonce($_POST['membresia_nonce'], 'estoicosgym_membresia_nonce')) {
        if (isset($_POST['membresia_precio'])) {
            update_post_meta($post_id, '_membresia_precio', sanitize_text_field($_POST['membresia_precio']));
        }
        if (isset($_POST['membresia_periodo'])) {
            update_post_meta($post_id, '_membresia_periodo', sanitize_text_field($_POST['membresia_periodo']));
        }
        update_post_meta($post_id, '_membresia_destacada', isset($_POST['membresia_destacada']) ? '1' : '0');
        if (isset($_POST['membresia_caracteristicas'])) {
            update_post_meta($post_id, '_membresia_caracteristicas', sanitize_textarea_field($_POST['membresia_caracteristicas']));
        }
    }
    
    // Testimonio
    if (isset($_POST['testimonio_nonce']) && wp_verify_nonce($_POST['testimonio_nonce'], 'estoicosgym_testimonio_nonce')) {
        if (isset($_POST['testimonio_cliente'])) {
            update_post_meta($post_id, '_testimonio_cliente', sanitize_text_field($_POST['testimonio_cliente']));
        }
        if (isset($_POST['testimonio_cargo'])) {
            update_post_meta($post_id, '_testimonio_cargo', sanitize_text_field($_POST['testimonio_cargo']));
        }
    }
}
add_action('save_post', 'estoicosgym_save_meta_boxes');

/**
 * Contact Form Handler (AJAX)
 */
function estoicosgym_contact_form_handler() {
    check_ajax_referer('estoicosgym_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_textarea_field($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(array('message' => 'Por favor complete todos los campos requeridos.'));
    }
    
    // Email al administrador
    $to = get_option('admin_email');
    $subject = 'Nuevo contacto desde EstoicosGym - ' . $name;
    $body = "Nombre: $name\n";
    $body .= "Email: $email\n";
    $body .= "Teléfono: $phone\n\n";
    $body .= "Mensaje:\n$message";
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    if (wp_mail($to, $subject, $body, $headers)) {
        wp_send_json_success(array('message' => '¡Mensaje enviado! Nos pondremos en contacto contigo pronto.'));
    } else {
        wp_send_json_error(array('message' => 'Error al enviar el mensaje. Por favor intenta de nuevo.'));
    }
}
add_action('wp_ajax_estoicosgym_contact', 'estoicosgym_contact_form_handler');
add_action('wp_ajax_nopriv_estoicosgym_contact', 'estoicosgym_contact_form_handler');

/**
 * Widgets
 */
function estoicosgym_widgets_init() {
    register_sidebar(array(
        'name'          => __('Footer Widget 1', 'estoicosgym'),
        'id'            => 'footer-1',
        'description'   => __('Widget area para el footer', 'estoicosgym'),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'estoicosgym_widgets_init');

/**
 * Helper: Get theme option
 */
function estoicosgym_get_option($option, $default = '') {
    return get_theme_mod($option, $default);
}

/**
 * Hide admin bar for non-admins
 */
if (!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
}
