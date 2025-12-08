<?php
/**
 * Render all steps
 */

namespace PixelGallery\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="bdt-stpg-header">
	<div class="bdt-shape-elements">
		<div class="bdt-shape bdt-shape-circle"></div>
		<div class="bdt-shape bdt-shape-square"></div>
		<div class="bdt-shape bdt-shape-triangle"></div>
		<div class="bdt-shape bdt-shape-dots"></div>
		<div class="bdt-shape bdt-shape-ring"></div>
		<div class="bdt-shape bdt-shape-plus"></div>
	</div>
	
	<div class="bdt-wizard-progress-header">
		<ul class="bdt-wizard-progress">
			<li class="bdt-wizard-progress-item active" data-step="welcome"><?php esc_html_e( 'Welcome', 'pixel-gallery' ); ?></li>
			<li class="bdt-wizard-progress-item" data-step="features"><?php esc_html_e( 'Choose Features', 'pixel-gallery' ); ?></li>
			<li class="bdt-wizard-progress-item" data-step="integration"><?php esc_html_e( 'Integration', 'pixel-gallery' ); ?></li>
			<li class="bdt-wizard-progress-item" data-step="finish"><?php esc_html_e( 'Good to Go', 'pixel-gallery' ); ?></li>
		</ul>
	</div>
	<div class="bdt-stpg-content">
		<div class="bdt-wizard-container">
			<?php
			require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/welcome.php';
			require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/features.php';
			require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/integration.php';
			require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/good-to-go.php';
			?>
		</div>
	</div>
</div>