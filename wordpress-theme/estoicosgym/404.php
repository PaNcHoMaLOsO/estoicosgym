<?php
/**
 * 404 Page Template
 * 
 * @package EstoicosGym
 */

get_header();
?>

<section class="error-404-section">
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-dumbbell"></i>
            </div>
            
            <h1 class="error-title">404</h1>
            <h2 class="error-subtitle">Página no encontrada</h2>
            
            <p class="error-description">
                ¡Ups! Parece que esta página se fue a entrenar y no volvió. 
                No te preocupes, puedes volver al inicio o explorar nuestros servicios.
            </p>
            
            <div class="error-buttons">
                <a href="<?php echo home_url('/'); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
                <a href="<?php echo home_url('/#contacto'); ?>" class="btn btn-outline btn-lg">
                    <i class="fas fa-phone"></i> Contactar
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.error-404-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    padding: 4rem 2rem;
}

.error-container {
    max-width: 600px;
    text-align: center;
}

.error-icon {
    font-size: 5rem;
    color: var(--accent-color);
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
}

.error-title {
    font-size: 8rem;
    font-weight: 800;
    color: var(--white);
    margin: 0;
    line-height: 1;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.error-subtitle {
    font-size: 2rem;
    color: var(--accent-color);
    margin: 1rem 0;
}

.error-description {
    font-size: 1.1rem;
    color: var(--text-muted);
    margin: 2rem 0;
    line-height: 1.7;
}

.error-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

@media (max-width: 576px) {
    .error-title {
        font-size: 5rem;
    }
    
    .error-subtitle {
        font-size: 1.5rem;
    }
    
    .error-buttons {
        flex-direction: column;
    }
    
    .error-buttons .btn {
        width: 100%;
    }
}
</style>

<?php get_footer(); ?>
