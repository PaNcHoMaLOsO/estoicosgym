</main><!-- End Main -->

<!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="<?php echo home_url('/'); ?>" class="footer-logo">
                    <span class="logo-text">ESTOICOS<span class="logo-accent">GYM</span></span>
                </a>
                <p class="footer-description">
                    Forja tu mejor versión con disciplina estoica. Más de 10 años transformando vidas a través del entrenamiento físico y mental.
                </p>
                <div class="footer-social">
                    <?php if ($instagram = estoicosgym_get_option('social_instagram')): ?>
                        <a href="<?php echo esc_url($instagram); ?>" class="social-link" target="_blank" rel="noopener">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($facebook = estoicosgym_get_option('social_facebook')): ?>
                        <a href="<?php echo esc_url($facebook); ?>" class="social-link" target="_blank" rel="noopener">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($whatsapp = estoicosgym_get_option('social_whatsapp')): ?>
                        <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp)); ?>" class="social-link" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-links">
                <h4 class="footer-title">Enlaces Rápidos</h4>
                <ul class="footer-list">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#servicios">Servicios</a></li>
                    <li><a href="#membresias">Membresías</a></li>
                    <li><a href="#horarios">Horarios</a></li>
                    <li><a href="#galeria">Galería</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </div>
            
            <!-- Services -->
            <div class="footer-links">
                <h4 class="footer-title">Servicios</h4>
                <ul class="footer-list">
                    <li><a href="#servicios">Musculación</a></li>
                    <li><a href="#servicios">Cardio Zone</a></li>
                    <li><a href="#servicios">CrossFit</a></li>
                    <li><a href="#servicios">Entrenamiento Personal</a></li>
                    <li><a href="#servicios">Clases Grupales</a></li>
                    <li><a href="#servicios">Nutrición</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="footer-contact">
                <h4 class="footer-title">Contacto</h4>
                <ul class="footer-contact-list">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo esc_html(estoicosgym_get_option('gym_address', 'Av. Principal #123, Santiago')); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', estoicosgym_get_option('gym_phone', '+56912345678'))); ?>">
                            <?php echo esc_html(estoicosgym_get_option('gym_phone', '+56 9 1234 5678')); ?>
                        </a>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo esc_attr(estoicosgym_get_option('gym_email', 'contacto@estoicosgym.cl')); ?>">
                            <?php echo esc_html(estoicosgym_get_option('gym_email', 'contacto@estoicosgym.cl')); ?>
                        </a>
                    </li>
                </ul>
                
                <!-- Horario -->
                <div class="footer-hours">
                    <h5>Horarios de Atención</h5>
                    <p>Lun - Vie: 6:00 AM - 10:00 PM</p>
                    <p>Sáb: 8:00 AM - 6:00 PM</p>
                    <p>Dom: 9:00 AM - 2:00 PM</p>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> EstoicosGym. Todos los derechos reservados.</p>
            <p class="footer-credits">
                Desarrollado con <i class="fas fa-heart" style="color: var(--accent-color);"></i> por EstoicosGym
            </p>
        </div>
    </div>
</footer>

<!-- Hidden Admin Access -->
<?php if (estoicosgym_get_option('show_admin_button', false)): ?>
    <a href="<?php echo esc_url(estoicosgym_get_option('admin_panel_url', '/sistema-admin')); ?>" 
       class="admin-access-hidden" 
       title="Panel de Administración"
       target="_blank">
        <i class="fas fa-cog"></i>
    </a>
<?php endif; ?>

<!-- Back to Top -->
<a href="#" class="back-to-top" id="back-to-top">
    <i class="fas fa-chevron-up"></i>
</a>

<?php wp_footer(); ?>
</body>
</html>
