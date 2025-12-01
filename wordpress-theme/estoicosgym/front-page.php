<?php
/**
 * Front Page Template
 * 
 * @package EstoicosGym
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero" id="inicio">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <?php 
        $hero_image_id = estoicosgym_get_option('hero_image');
        if ($hero_image_id): 
            $hero_image_url = wp_get_attachment_image_url($hero_image_id, 'full');
        ?>
            <img src="<?php echo esc_url($hero_image_url); ?>" alt="EstoicosGym Hero" class="hero-bg-image">
        <?php else: ?>
            <div class="hero-bg-placeholder"></div>
        <?php endif; ?>
    </div>
    
    <div class="hero-container">
        <div class="hero-content">
            <span class="hero-badge">
                <i class="fas fa-fire"></i> Gimnasio Premium
            </span>
            
            <h1 class="hero-title">
                <?php echo esc_html(estoicosgym_get_option('hero_title', 'Forja tu mejor versión')); ?>
            </h1>
            
            <p class="hero-description">
                <?php echo esc_html(estoicosgym_get_option('hero_subtitle', 'Entrena con disciplina estoica. Transforma tu cuerpo y mente con nuestros programas personalizados y equipamiento de última generación.')); ?>
            </p>
            
            <div class="hero-buttons">
                <a href="#membresias" class="btn btn-primary btn-lg">
                    <i class="fas fa-dumbbell"></i> Ver Membresías
                </a>
                <a href="#contacto" class="btn btn-outline btn-lg">
                    <i class="fas fa-phone"></i> Contáctanos
                </a>
            </div>
            
            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number" data-count="500">0</span>
                    <span class="stat-plus">+</span>
                    <span class="stat-label">Miembros Activos</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="10">0</span>
                    <span class="stat-plus">+</span>
                    <span class="stat-label">Años de Experiencia</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="15">0</span>
                    <span class="stat-plus">+</span>
                    <span class="stat-label">Entrenadores</span>
                </div>
            </div>
        </div>
        
        <div class="hero-image">
            <div class="hero-image-frame">
                <?php if ($hero_image_id): ?>
                    <img src="<?php echo esc_url($hero_image_url); ?>" alt="Entrenamiento EstoicosGym">
                <?php else: ?>
                    <div class="hero-image-placeholder">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <a href="#servicios">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="servicios">
    <div class="services-container">
        <div class="section-header">
            <span class="section-badge">Nuestros Servicios</span>
            <h2 class="section-title">Todo lo que necesitas para <span class="highlight">transformarte</span></h2>
            <p class="section-description">Contamos con instalaciones de primer nivel y servicios diseñados para maximizar tu rendimiento</p>
        </div>
        
        <div class="services-grid">
            <!-- Service 1 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3 class="service-title">Musculación</h3>
                <p class="service-description">Equipamiento de última generación para trabajar todos los grupos musculares. Máquinas Hammer Strength y peso libre.</p>
            </div>
            
            <!-- Service 2 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="service-title">Cardio Zone</h3>
                <p class="service-description">Amplia área de cardio con cintas, bicicletas, elípticas y escaladoras de las mejores marcas.</p>
            </div>
            
            <!-- Service 3 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-fire-alt"></i>
                </div>
                <h3 class="service-title">CrossFit</h3>
                <p class="service-description">Box de CrossFit equipado con barras olímpicas, kettlebells, wall balls y todo lo necesario para WODs intensos.</p>
            </div>
            
            <!-- Service 4 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3 class="service-title">Entrenamiento Personal</h3>
                <p class="service-description">Entrenadores certificados que diseñan rutinas personalizadas según tus objetivos y condición física.</p>
            </div>
            
            <!-- Service 5 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="service-title">Clases Grupales</h3>
                <p class="service-description">Spinning, Zumba, Yoga, Pilates, Funcional y más. Variedad de horarios para todos los niveles.</p>
            </div>
            
            <!-- Service 6 -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-apple-alt"></i>
                </div>
                <h3 class="service-title">Asesoría Nutricional</h3>
                <p class="service-description">Planes de alimentación personalizados para complementar tu entrenamiento y alcanzar resultados óptimos.</p>
            </div>
        </div>
    </div>
</section>

<!-- Memberships Section -->
<section class="memberships" id="membresias">
    <div class="memberships-container">
        <div class="section-header">
            <span class="section-badge">Membresías</span>
            <h2 class="section-title">Elige el plan <span class="highlight">perfecto para ti</span></h2>
            <p class="section-description">Planes flexibles diseñados para adaptarse a tu estilo de vida y objetivos</p>
        </div>
        
        <div class="memberships-grid">
            <?php
            $membresias = new WP_Query(array(
                'post_type'      => 'membresia',
                'posts_per_page' => 4,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ));
            
            if ($membresias->have_posts()):
                while ($membresias->have_posts()): $membresias->the_post();
                    $precio = get_post_meta(get_the_ID(), '_membresia_precio', true);
                    $periodo = get_post_meta(get_the_ID(), '_membresia_periodo', true);
                    $destacada = get_post_meta(get_the_ID(), '_membresia_destacada', true);
                    $caracteristicas = get_post_meta(get_the_ID(), '_membresia_caracteristicas', true);
                    $features = array_filter(explode("\n", $caracteristicas));
            ?>
                <div class="membership-card<?php echo $destacada ? ' featured' : ''; ?>">
                    <?php if ($destacada): ?>
                        <span class="membership-badge">Más Popular</span>
                    <?php endif; ?>
                    
                    <h3 class="membership-title"><?php the_title(); ?></h3>
                    
                    <div class="membership-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount"><?php echo number_format($precio, 0, ',', '.'); ?></span>
                        <span class="price-period">/<?php echo esc_html($periodo); ?></span>
                    </div>
                    
                    <div class="membership-description">
                        <?php the_content(); ?>
                    </div>
                    
                    <?php if (!empty($features)): ?>
                    <ul class="membership-features">
                        <?php foreach ($features as $feature): ?>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php echo esc_html(trim($feature)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    
                    <a href="#contacto" class="btn <?php echo $destacada ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                        ¡Inscribirse Ahora!
                    </a>
                </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
                // Default memberships if no custom posts
            ?>
                <!-- Plan Básico -->
                <div class="membership-card">
                    <h3 class="membership-title">Plan Básico</h3>
                    <div class="membership-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">25.000</span>
                        <span class="price-period">/mes</span>
                    </div>
                    <ul class="membership-features">
                        <li><i class="fas fa-check"></i><span>Acceso a sala de musculación</span></li>
                        <li><i class="fas fa-check"></i><span>Área de cardio</span></li>
                        <li><i class="fas fa-check"></i><span>Duchas y vestidores</span></li>
                        <li><i class="fas fa-check"></i><span>Horario limitado (9am-5pm)</span></li>
                    </ul>
                    <a href="#contacto" class="btn btn-outline btn-block">¡Inscribirse Ahora!</a>
                </div>
                
                <!-- Plan Full -->
                <div class="membership-card featured">
                    <span class="membership-badge">Más Popular</span>
                    <h3 class="membership-title">Plan Full</h3>
                    <div class="membership-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">35.000</span>
                        <span class="price-period">/mes</span>
                    </div>
                    <ul class="membership-features">
                        <li><i class="fas fa-check"></i><span>Acceso completo al gimnasio</span></li>
                        <li><i class="fas fa-check"></i><span>Todas las clases grupales</span></li>
                        <li><i class="fas fa-check"></i><span>Horario extendido</span></li>
                        <li><i class="fas fa-check"></i><span>Evaluación física mensual</span></li>
                        <li><i class="fas fa-check"></i><span>1 sesión PT/mes</span></li>
                    </ul>
                    <a href="#contacto" class="btn btn-primary btn-block">¡Inscribirse Ahora!</a>
                </div>
                
                <!-- Plan Premium -->
                <div class="membership-card">
                    <h3 class="membership-title">Plan Premium</h3>
                    <div class="membership-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount">50.000</span>
                        <span class="price-period">/mes</span>
                    </div>
                    <ul class="membership-features">
                        <li><i class="fas fa-check"></i><span>Todo lo del Plan Full</span></li>
                        <li><i class="fas fa-check"></i><span>4 sesiones PT/mes</span></li>
                        <li><i class="fas fa-check"></i><span>Plan nutricional</span></li>
                        <li><i class="fas fa-check"></i><span>Acceso 24/7</span></li>
                        <li><i class="fas fa-check"></i><span>Locker personal</span></li>
                    </ul>
                    <a href="#contacto" class="btn btn-outline btn-block">¡Inscribirse Ahora!</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Schedule Section -->
<section class="schedule" id="horarios">
    <div class="schedule-container">
        <div class="section-header">
            <span class="section-badge">Horarios</span>
            <h2 class="section-title">Nuestras <span class="highlight">clases grupales</span></h2>
            <p class="section-description">Encuentra el horario perfecto para tu rutina de entrenamiento</p>
        </div>
        
        <div class="schedule-table-wrapper">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                        <th>Sábado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="time-cell">07:00</td>
                        <td><span class="class-spinning">Spinning</span></td>
                        <td><span class="class-functional">Funcional</span></td>
                        <td><span class="class-spinning">Spinning</span></td>
                        <td><span class="class-functional">Funcional</span></td>
                        <td><span class="class-spinning">Spinning</span></td>
                        <td><span class="class-yoga">Yoga</span></td>
                    </tr>
                    <tr>
                        <td class="time-cell">09:00</td>
                        <td><span class="class-yoga">Yoga</span></td>
                        <td><span class="class-pilates">Pilates</span></td>
                        <td><span class="class-yoga">Yoga</span></td>
                        <td><span class="class-pilates">Pilates</span></td>
                        <td><span class="class-yoga">Yoga</span></td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                    </tr>
                    <tr>
                        <td class="time-cell">11:00</td>
                        <td><span class="class-functional">Funcional</span></td>
                        <td><span class="class-zumba">Zumba</span></td>
                        <td><span class="class-functional">Funcional</span></td>
                        <td><span class="class-zumba">Zumba</span></td>
                        <td><span class="class-functional">Funcional</span></td>
                        <td><span class="class-zumba">Zumba</span></td>
                    </tr>
                    <tr>
                        <td class="time-cell">17:00</td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                        <td><span class="class-spinning">Spinning</span></td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                        <td><span class="class-spinning">Spinning</span></td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td class="time-cell">19:00</td>
                        <td><span class="class-zumba">Zumba</span></td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                        <td><span class="class-zumba">Zumba</span></td>
                        <td><span class="class-crossfit">CrossFit</span></td>
                        <td><span class="class-zumba">Zumba</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td class="time-cell">20:30</td>
                        <td><span class="class-pilates">Pilates</span></td>
                        <td><span class="class-yoga">Yoga</span></td>
                        <td><span class="class-pilates">Pilates</span></td>
                        <td><span class="class-yoga">Yoga</span></td>
                        <td><span class="class-pilates">Pilates</span></td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="schedule-legend">
            <span class="legend-item"><span class="legend-color class-spinning"></span> Spinning</span>
            <span class="legend-item"><span class="legend-color class-yoga"></span> Yoga</span>
            <span class="legend-item"><span class="legend-color class-functional"></span> Funcional</span>
            <span class="legend-item"><span class="legend-color class-crossfit"></span> CrossFit</span>
            <span class="legend-item"><span class="legend-color class-zumba"></span> Zumba</span>
            <span class="legend-item"><span class="legend-color class-pilates"></span> Pilates</span>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="gallery" id="galeria">
    <div class="gallery-container">
        <div class="section-header">
            <span class="section-badge">Galería</span>
            <h2 class="section-title">Nuestras <span class="highlight">instalaciones</span></h2>
            <p class="section-description">Conoce nuestro espacio de entrenamiento</p>
        </div>
        
        <div class="gallery-grid">
            <?php
            $galeria = new WP_Query(array(
                'post_type'      => 'galeria',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));
            
            if ($galeria->have_posts()):
                while ($galeria->have_posts()): $galeria->the_post();
                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'gallery-thumb');
                    $full = get_the_post_thumbnail_url(get_the_ID(), 'full');
            ?>
                <div class="gallery-item">
                    <a href="<?php echo esc_url($full); ?>" class="gallery-link" data-lightbox="gallery">
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>">
                        <div class="gallery-overlay">
                            <i class="fas fa-expand"></i>
                        </div>
                    </a>
                </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
                // Placeholder images
                for ($i = 1; $i <= 6; $i++):
            ?>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <i class="fas fa-image"></i>
                        <span>Imagen <?php echo $i; ?></span>
                    </div>
                </div>
            <?php
                endfor;
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials" id="testimonios">
    <div class="testimonials-container">
        <div class="section-header">
            <span class="section-badge">Testimonios</span>
            <h2 class="section-title">Lo que dicen <span class="highlight">nuestros miembros</span></h2>
            <p class="section-description">Historias de transformación y éxito</p>
        </div>
        
        <div class="testimonials-slider">
            <?php
            $testimonios = new WP_Query(array(
                'post_type'      => 'testimonio',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));
            
            if ($testimonios->have_posts()):
                while ($testimonios->have_posts()): $testimonios->the_post();
                    $cliente = get_post_meta(get_the_ID(), '_testimonio_cliente', true);
                    $cargo = get_post_meta(get_the_ID(), '_testimonio_cargo', true);
            ?>
                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <div class="testimonial-content">
                        <?php the_content(); ?>
                    </div>
                    <div class="testimonial-author">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="author-avatar">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </div>
                        <?php else: ?>
                            <div class="author-avatar placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="author-info">
                            <h4 class="author-name"><?php echo esc_html($cliente ?: get_the_title()); ?></h4>
                            <span class="author-role"><?php echo esc_html($cargo); ?></span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
                // Default testimonials
            ?>
                <div class="testimonial-card">
                    <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                    <div class="testimonial-content">
                        <p>Llevo 2 años entrenando en EstoicosGym y ha sido una transformación total. El ambiente, los entrenadores y las instalaciones son de primer nivel.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar placeholder"><i class="fas fa-user"></i></div>
                        <div class="author-info">
                            <h4 class="author-name">María González</h4>
                            <span class="author-role">Miembro desde 2022</span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                    <div class="testimonial-content">
                        <p>El mejor gimnasio de la zona. El entrenamiento personal me ayudó a perder 15 kilos en 6 meses. ¡100% recomendado!</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar placeholder"><i class="fas fa-user"></i></div>
                        <div class="author-info">
                            <h4 class="author-name">Carlos Muñoz</h4>
                            <span class="author-role">Miembro desde 2023</span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                    <div class="testimonial-content">
                        <p>Las clases grupales son increíbles. El CrossFit me cambió la vida. Los coaches siempre motivan y corrigen la técnica.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar placeholder"><i class="fas fa-user"></i></div>
                        <div class="author-info">
                            <h4 class="author-name">Andrea Soto</h4>
                            <span class="author-role">Miembro desde 2021</span>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact" id="contacto">
    <div class="contact-container">
        <div class="section-header">
            <span class="section-badge">Contacto</span>
            <h2 class="section-title">¿Listo para <span class="highlight">empezar?</span></h2>
            <p class="section-description">Contáctanos y comienza tu transformación hoy mismo</p>
        </div>
        
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <form id="contact-form" class="contact-form">
                    <div class="form-group">
                        <label for="contact-name">Nombre completo *</label>
                        <input type="text" id="contact-name" name="name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-email">Email *</label>
                            <input type="email" id="contact-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact-phone">Teléfono</label>
                            <input type="tel" id="contact-phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact-message">Mensaje *</label>
                        <textarea id="contact-message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                    
                    <div class="form-message" id="form-message"></div>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div class="contact-info-wrapper">
                <div class="contact-info-card">
                    <h3>Información de Contacto</h3>
                    
                    <ul class="contact-list">
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Dirección</h4>
                                <p><?php echo esc_html(estoicosgym_get_option('gym_address', 'Av. Principal #123, Santiago')); ?></p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Teléfono</h4>
                                <p>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', estoicosgym_get_option('gym_phone', '+56912345678'))); ?>">
                                        <?php echo esc_html(estoicosgym_get_option('gym_phone', '+56 9 1234 5678')); ?>
                                    </a>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email</h4>
                                <p>
                                    <a href="mailto:<?php echo esc_attr(estoicosgym_get_option('gym_email', 'contacto@estoicosgym.cl')); ?>">
                                        <?php echo esc_html(estoicosgym_get_option('gym_email', 'contacto@estoicosgym.cl')); ?>
                                    </a>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Horario de Atención</h4>
                                <p>Lun - Vie: 6:00 AM - 10:00 PM</p>
                                <p>Sáb: 8:00 AM - 6:00 PM</p>
                                <p>Dom: 9:00 AM - 2:00 PM</p>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- WhatsApp Button -->
                    <?php if ($whatsapp = estoicosgym_get_option('social_whatsapp')): ?>
                        <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp)); ?>?text=Hola,%20me%20interesa%20obtener%20información%20sobre%20las%20membresías" 
                           class="btn btn-whatsapp btn-lg btn-block" 
                           target="_blank">
                            <i class="fab fa-whatsapp"></i> Escríbenos por WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Map Placeholder -->
                <div class="contact-map">
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>Mapa de ubicación</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
