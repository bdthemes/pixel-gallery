<?php
/**
 * Welcome Step
 */

namespace PixelGallery\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="bdt-wizard-step bdt-text-center active" data-step="welcome">
    <div class="bdt-welcome-header">
        <div class="bdt-logo-container">
            <img src="<?php echo BDTPG_ASSETS_URL . 'images/logo.svg'; ?>" alt="Pixel Gallery Logo" class="bdt-logo">
        </div>
        <h2><?php esc_html_e( 'Welcome to Pixel Gallery', 'pixel-gallery' ); ?></h2>
        <p><?php esc_html_e( 'Thank you for choosing Pixel Gallery, a leading addon that provides a total web design solution for you. This quick setup wizard will help you configure the basic settings and get you started.', 'pixel-gallery' ); ?></p>
    </div>
    
    <div class="bdt-welcome-features">
        <div class="bdt-features-grid">
            <div class="bdt-feature-item">
                <div class="bdt-feature-icon">
                    <span class="dashicons dashicons-admin-customizer"></span>
                </div>
                <h3><?php esc_html_e( '300+ Widgets', 'pixel-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Powerful elements for unlimited design possibilities', 'pixel-gallery' ); ?></p>
            </div>
            <div class="bdt-feature-item">
                <div class="bdt-feature-icon">
                    <span class="dashicons dashicons-layout"></span>
                </div>
                <h3><?php esc_html_e( 'Ready Templates', 'pixel-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Professional templates to jumpstart your projects', 'pixel-gallery' ); ?></p>
            </div>
            <div class="bdt-feature-item">
                <div class="bdt-feature-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <h3><?php esc_html_e( 'Fast & Optimized', 'pixel-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Built with performance in mind for lightning-fast websites', 'pixel-gallery' ); ?></p>
            </div>
        </div>
    </div>

    <div class="bdt-wizard-navigation">
        <button class="bdt-button bdt-button-primary bdt-wizard-next" data-step="features">
            <?php esc_html_e( 'Get Started', 'pixel-gallery' ); ?>
            <span><i class="dashicons dashicons-arrow-right-alt"></i></span>
        </button>
    </div>
</div>
