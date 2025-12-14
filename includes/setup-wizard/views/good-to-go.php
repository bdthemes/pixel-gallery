<?php
/**
 * Complete Step
 */

namespace PixelGallery\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templates_path = BDTPG_INC_PATH . 'setup-wizard/assets/data.json';
$templates      = json_decode( file_get_contents( $templates_path ), true );

?>
<div class="bdt-wizard-step bdt-text-center" data-step="finish">

    <div class="bdt-templates-section">
		<div class="bdt-success-icon">
            <i class="dashicons dashicons-yes-alt"></i>
        </div>

        <h3><?php esc_html_e( 'Ready-to-Use Templates', 'pixel-gallery' ); ?></h3>
        <p><?php esc_html_e( 'Get a head start with these professional templates. Just click on Import to add them to your site.', 'pixel-gallery' ); ?></p>
        
        <div class="template-list">
            <?php foreach ( $templates as $template ) : ?>
            <?php
                $assets_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'setup-wizard/assets';
                $importUrl = $assets_url . $template['import_url'];
                $thumbnailUrl = $assets_url . $template['thumbnail'];
                $extension = pathinfo($importUrl, PATHINFO_EXTENSION);
                if (!$extension || !in_array(strtolower($extension), ['json', 'zip'])) {
                    continue;
                }
                $extension = strtolower($extension);
            ?>
                <div class="choose-template <?php echo $extension ?> <?php echo $extension =='zip' ? 'bdt-pg-import-temp-zip':'bdt-pg-import-temp-json' ?>" data-import-url="<?php echo esc_url( $importUrl ); ?>">
                    <div class="template-image">
                        <img src="<?php echo esc_url( $thumbnailUrl ); ?>" alt="<?php echo esc_attr( $template['title'] ); ?>">
                        <div class="template-actions">
                            <a href="<?php echo esc_url( $template['demo_url'] ); ?>" target="_blank" class="template-preview">
                                <i class="dashicons dashicons-visibility"></i> <?php esc_html_e( 'Preview', 'pixel-gallery' ); ?>
                            </a>
                            <button class="template-import">
                                <i class="dashicons dashicons-download"></i> <?php esc_html_e( 'Import', 'pixel-gallery' ); ?>
                            </button>
                        </div>
                    </div>
                    <div class="template-title"><?php echo esc_html( $template['title'] ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="bdt-help-resources">
        <h3><?php esc_html_e( 'Helpful Resources', 'pixel-gallery' ); ?></h3>
        
        <div class="bdt-resources-grid">
            <a href="https://bdthemes.com/all-knowledge-base-of-pixel-gallery/" target="_blank" class="bdt-resource-item">
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