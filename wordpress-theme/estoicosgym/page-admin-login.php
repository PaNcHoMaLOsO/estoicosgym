<?php
/**
 * Template Name: Admin Login
 * 
 * Hidden admin login page that redirects to Laravel panel
 * 
 * @package EstoicosGym
 */

// Get the admin panel URL from customizer or use default
$admin_url = estoicosgym_get_option('admin_panel_url', '/sistema-admin');

// If it's a relative URL, make it absolute
if (strpos($admin_url, 'http') !== 0) {
    $admin_url = home_url($admin_url);
}

// Redirect to the Laravel admin panel
wp_redirect($admin_url);
exit;
