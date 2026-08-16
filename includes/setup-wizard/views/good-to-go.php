<?php
/**
 * Complete Step
 */

namespace PixelGallery\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="bdt-wizard-step bdt-text-center" data-step="finish">

    <div class="bdt-setup-complete">
		<div class="bdt-success-icon">
            <i class="dashicons dashicons-yes-alt"></i>
        </div>

        <h3><?php esc_html_e( 'You\'re All Set!', 'pixel-gallery' ); ?></h3>
        <p><?php esc_html_e( 'Pixel Gallery is ready to use. Open the Elementor editor and drop a Pixel Gallery widget onto any page to get started.', 'pixel-gallery' ); ?></p>
    </div>

    <div class="bdt-help-resources">
        <h3><?php esc_html_e( 'Helpful Resources', 'pixel-gallery' ); ?></h3>
        
        <div class="bdt-resources-grid">
            <a href="https://bdthemes.com/knowledge-base/pixel-gallery/" target="_blank" class="bdt-resource-item">
                <div class="resource-icon">
                    <i class="dashicons dashicons-book"></i>
                </div>
                <h4><?php esc_html_e( 'Documentation', 'pixel-gallery' ); ?></h4>
                <p><?php esc_html_e( 'Find detailed guides and documentation', 'pixel-gallery' ); ?></p>
            </a>
            
            <a href="https://bdthemes.com/support/" target="_blank" class="bdt-resource-item">
                <div class="resource-icon">
                    <i class="dashicons dashicons-sos"></i>
                </div>
                <h4><?php esc_html_e( 'Get Support', 'pixel-gallery' ); ?></h4>
                <p><?php esc_html_e( 'Contact our customer support team', 'pixel-gallery' ); ?></p>
            </a>
            
            <a href="https://www.youtube.com/playlist?list=PLP0S85GEw7DPv5T-Ara11Zvplmk4ty0jy" target="_blank" class="bdt-resource-item">
                <div class="resource-icon">
                    <i class="dashicons dashicons-video-alt3"></i>
                </div>
                <h4><?php esc_html_e( 'Video Tutorials', 'pixel-gallery' ); ?></h4>
                <p><?php esc_html_e( 'Watch tutorials on our YouTube channel', 'pixel-gallery' ); ?></p>
            </a>
        </div>
    </div>
    
	<div class="bdt-flex bdt-flex-between bdt-flex-wrap">
		<div class="bdt-wizard-navigation">
			<button class="bdt-button bdt-button-secondary bdt-wizard-prev" data-step="integration">
				<span><i class="dashicons dashicons-arrow-left-alt"></i></span>
				<?php esc_html_e( 'Previous Step', 'pixel-gallery' ); ?>
			</button>
		</div>
	
		<div class="bdt-next-steps">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pixel_gallery_options' ) ); ?>" class="bdt-button bdt-button-primary">
				<i class="dashicons dashicons-dashboard"></i>
				<?php esc_html_e( 'Go to Pixel Gallery Dashboard', 'pixel-gallery' ); ?>
			</a>
			
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="bdt-button bdt-button-secondary">
				<i class="dashicons dashicons-edit"></i>
				<?php esc_html_e( 'Edit Your Pages', 'pixel-gallery' ); ?>
			</a>
		</div>
	</div>

</div>