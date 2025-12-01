<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EstoicosGym - Forja tu mejor versión. Gimnasio con equipamiento de última generación y entrenadores certificados.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ESTOICOSGYM_URI; ?>/assets/images/favicon.ico">
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header -->
<header class="header" id="header">
    <nav class="nav">
        <div class="nav-container">
            <!-- Logo -->
            <a href="<?php echo home_url('/'); ?>" class="nav-logo">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <span class="logo-text">ESTOICOS<span class="logo-accent">GYM</span></span>
                <?php endif; ?>
            </a>
            
            <!-- Navigation Menu -->
            <div class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="#inicio" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#servicios" class="nav-link">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a href="#membresias" class="nav-link">Membresías</a>
                    </li>
                    <li class="nav-item">
                        <a href="#horarios" class="nav-link">Horarios</a>
                    </li>
                    <li class="nav-item">
                        <a href="#galeria" class="nav-link">Galería</a>
                    </li>
                    <li class="nav-item">
                        <a href="#testimonios" class="nav-link">Testimonios</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contacto" class="nav-link">Contacto</a>
                    </li>
                </ul>
                
                <!-- CTA Button -->
                <div class="nav-cta">
                    <a href="#contacto" class="btn btn-primary">¡Únete Ahora!</a>
                </div>
                
                <!-- Close button for mobile -->
                <div class="nav-close" id="nav-close">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            
            <!-- Mobile Toggle -->
            <div class="nav-toggle" id="nav-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
</header>

<!-- Main Content -->
<main class="main">
