<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelGallery\Biggopties;
use PixelGallery\Utils;
use PixelGallery\Admin\ModuleService;
use Elementor\Modules\Usage\Module;
use Elementor\Tracker;

/**
 * Pixel Gallery Admin Settings Class
 */

class PixelGallery_Admin_Settings
{

	public static $modules_list = null;
	public static $modules_names = null;

	public static $modules_list_only_widgets = null;
	public static $modules_names_only_widgets = null;


	const PAGE_ID = 'pixel_gallery_options';

	private $settings_api;

	public $responseObj;
	public $licenseMessage;
	public $showMessage = false;
	private $is_activated = false;

	function __construct()
	{
		$this->settings_api = new PixelGallery_Settings_API;

		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', [$this, 'admin_menu'], 201);

		if (!Tracker::is_allow_track()) {
			add_action('admin_notices', [$this, 'allow_tracker_activate_biggopti'], 10, 3);
		}

		// Plugin installation (admin only)
		add_action('wp_ajax_bdtpg_install_plugin', [$this, 'install_plugin_ajax']);
	}

	/**
	 * Get used widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */
	public static function get_used_widgets()
	{

		$used_widgets = array();

		if (class_exists('Elementor\Modules\Usage\Module')) {
			$module = Module::instance();

			// Elementor can emit notices for orphaned usage rows; suppress them locally
			// instead of touching the global error_reporting() level.
			$elements = @$module->get_formatted_usage('raw'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Third-party method may notice on stale usage data.

			$pg_widgets = self::get_pg_widgets_names();

			if (is_array($elements) || is_object($elements)) {
				foreach ($elements as $post_type => $data) {
					foreach ($data['elements'] as $element => $count) {
						if (in_array($element, $pg_widgets, true)) {
							if (isset($used_widgets[$element])) {
								$used_widgets[$element] += $count;
							} else {
								$used_widgets[$element] = $count;
							}
						}
					}
				}
			}
		}

		return $used_widgets;
	}

	/**
	 * Get used separate widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_used_only_widgets()
	{

		$used_widgets = array();

		if (class_exists('Elementor\Modules\Usage\Module')) {
			$module = Module::instance();

			// Elementor can emit notices for orphaned usage rows; suppress them locally
			// instead of touching the global error_reporting() level.
			$elements = @$module->get_formatted_usage('raw'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Third-party method may notice on stale usage data.

			$pg_widgets = self::get_pg_only_widgets();

			if (is_array($elements) || is_object($elements)) {
				foreach ($elements as $post_type => $data) {
					foreach ($data['elements'] as $element => $count) {
						if (in_array($element, $pg_widgets, true)) {
							if (isset($used_widgets[$element])) {
								$used_widgets[$element] += $count;
							} else {
								$used_widgets[$element] = $count;
							}
						}
					}
				}
			}
		}

		return $used_widgets;
	}

	/**
	 * Get unused widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_unused_widgets()
	{

		if (!current_user_can('install_plugins')) {
			die();
		}

		$pg_widgets = self::get_pg_widgets_names();

		$used_widgets = self::get_used_widgets();

		$unused_widgets = array_diff($pg_widgets, array_keys($used_widgets));

		return $unused_widgets;
	}

	/**
	 * Get unused separate widgets.
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_unused_only_widgets()
	{

		if (!current_user_can('install_plugins')) {
			die();
		}

		$pg_widgets = self::get_pg_only_widgets();

		$used_widgets = self::get_used_only_widgets();

		$unused_widgets = array_diff($pg_widgets, array_keys($used_widgets));

		return $unused_widgets;
	}

	/**
	 * Get widgets name
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_pg_widgets_names()
	{
		$names = self::$modules_names;

		if (null === $names) {
			$names = array_map(
				function ($item) {
					return isset($item['name']) ? 'pg-' . str_replace('_', '-', $item['name']) : 'none';
				},
				self::$modules_list
			);
		}

		return $names;
	}

	/**
	 * Get separate widgets name
	 *
	 * @access public
	 * @return array
	 * @since 6.0.0
	 *
	 */

	public static function get_pg_only_widgets()
	{
		$names = self::$modules_names_only_widgets;

		if (null === $names) {
			$names = array_map(
				function ($item) {
					return isset($item['name']) ? 'bdt-' . str_replace('_', '-', $item['name']) : 'none';
				},
				self::$modules_list_only_widgets
			);
		}

		return $names;
	}



	/**
	 * Get URL with page id
	 *
	 * @access public
	 *
	 */

	public static function get_url()
	{
		return admin_url('admin.php?page=' . self::PAGE_ID);
	}

	/**
	 * Init settings API
	 *
	 * @access public
	 *
	 */

	public function admin_init()
	{

		//set the settings
		$this->settings_api->set_sections($this->get_settings_sections());
		$this->settings_api->set_fields($this->pixel_gallery_admin_settings());

		//initialize settings
		$this->settings_api->admin_init();
		$this->pg_redirect_to_get_pro();

		if (_is_pg_pro_activated()) {
			$this->bdt_redirect_to_renew_link();
		}
	}

	/**
	 * Add Plugin Menus
	 *
	 * @access public
	 *
	 */

	// Redirect to Pixel Gallery Pro pricing page
	public function pg_redirect_to_get_pro()
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only menu routing check; no form data is processed and no state changes.
		if (isset($_GET['page']) && self::PAGE_ID . '_get_pro' === sanitize_text_field(wp_unslash($_GET['page']))) {
			wp_safe_redirect('https://pixelgallery.pro/pricing/');
			exit;
		}
	}

	/**
	 * Redirect to license renewal page
	 *
	 * @access public
	 *
	 */
	public function bdt_redirect_to_renew_link()
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only menu routing check; no form data is processed and no state changes.
		if (isset($_GET['page']) && self::PAGE_ID . '_license_renew' === sanitize_text_field(wp_unslash($_GET['page']))) {
			wp_safe_redirect('https://account.bdthemes.com/');
			exit;
		}
	}

	public function admin_menu()
	{
		add_menu_page(
			BDTPG_TITLE . ' ' . esc_html__('Dashboard', 'pixel-gallery'),
			BDTPG_TITLE,
			'manage_options',
			self::PAGE_ID,
			[$this, 'plugin_page'],
			$this->pixel_gallery_icon(),
			58
		);

		add_submenu_page(
			self::PAGE_ID,
			esc_html__('Dashboard', 'pixel-gallery'),
			esc_html__('Dashboard', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID,
			[$this, 'plugin_page'],
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTPG_TITLE,
			esc_html__('Core Widgets', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID . '#pixel_gallery_active_modules',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTPG_TITLE,
			esc_html__('Extensions', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID . '#pixel_gallery_elementor_extend',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTPG_TITLE,
			esc_html__('System Status', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID . '#pixel_gallery_analytics_system_req',
			[$this, 'plugin_page']
		);

		add_submenu_page(
			self::PAGE_ID,
			BDTPG_TITLE,
			esc_html__('Other Plugins', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID . '#pixel_gallery_other_plugins',
			[$this, 'plugin_page']
		);

	}

	/**
	 * Get SVG Icons of Pixel Gallery
	 *
	 * @access public
	 * @return string
	 */

	public function pixel_gallery_icon()
	{
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNS4zLjEsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCA1MDIuMiA1MDEuOCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTAyLjIgNTAxLjg7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxyZWN0IHg9Ijg4LjkiIHk9Ijk5IiBjbGFzcz0ic3QwIiB3aWR0aD0iMzQuMSIgaGVpZ2h0PSIzNC4xIi8+DQoJPHJlY3QgeD0iNTQuMiIgeT0iNTgiIGNsYXNzPSJzdDAiIHdpZHRoPSIyMS43IiBoZWlnaHQ9IjIxLjciLz4NCgk8cmVjdCB4PSI3MS40IiB5PSIyLjQiIGNsYXNzPSJzdDAiIHdpZHRoPSI5LjkiIGhlaWdodD0iOS45Ii8+DQoJPHJlY3QgeD0iOTkuNyIgeT0iMzUuNCIgY2xhc3M9InN0MCIgd2lkdGg9IjE0LjgiIGhlaWdodD0iMTQuOCIvPg0KCTxyZWN0IHg9Ijk4LjciIHk9IjE5NC4zIiBjbGFzcz0ic3QwIiB3aWR0aD0iMTQuOCIgaGVpZ2h0PSIxNC44Ii8+DQoJPHJlY3QgeD0iMTgyLjkiIHk9IjEyLjgiIGNsYXNzPSJzdDAiIHdpZHRoPSIxMi4zIiBoZWlnaHQ9IjEyLjMiLz4NCgk8cmVjdCB4PSIxNDEuMSIgeT0iMTQzLjYiIGNsYXNzPSJzdDAiIHdpZHRoPSI2MC40IiBoZWlnaHQ9IjYwLjQiLz4NCgk8cmVjdCB4PSIxNDMuMiIgeT0iNDYuNiIgY2xhc3M9InN0MCIgd2lkdGg9IjM1LjMiIGhlaWdodD0iMzUuMyIvPg0KCTxyZWN0IHg9IjU5LjciIHk9IjE1MS4xIiBjbGFzcz0ic3QwIiB3aWR0aD0iMjIiIGhlaWdodD0iMjIiLz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzk4LjIsNjIuNGMtMzMtMzIuNS03My40LTQ4LjgtMTIxLjMtNDguOGgtNDMuNnYzMi4yaC0yOS42djcyLjNoNzMuMmMxNy4xLDAsMzEuMyw2LjEsNDIuNiwxOC4yDQoJCWMxMS4xLDEyLjEsMTYuNywyNi45LDE2LjcsNDQuNnMtNS42LDMyLjUtMTYuNyw0NC42Yy0xMS4xLDEyLjEtMjUuMywxOC4yLTQyLjYsMTguMmgtNzMuMmwwLDBoLTYxLjV2NjQuOUg5Mi4zdjE5My4xaDExMS42VjM0OC4zDQoJCWg3My4yYzQ3LjksMCw4OC40LTE2LjMsMTIxLjMtNDguOHM0OS41LTcyLjEsNDkuNS0xMTguNUM0NDcuNywxMzQuNCw0MzEuMiw5NSwzOTguMiw2Mi40eiIvPg0KCTxyZWN0IHg9Ijc2LjIiIHk9IjI0My4zIiBjbGFzcz0ic3QwIiB3aWR0aD0iNDQuNSIgaGVpZ2h0PSI0NC41Ii8+DQo8L2c+DQo8L3N2Zz4NCg==';
	}

	/**
	 * Get SVG Icons of Pixel Gallery
	 *
	 * @access public
	 * @return array
	 */

	public function get_settings_sections()
	{
		$sections = [
			[
				'id' => 'pixel_gallery_active_modules',
				'title' => esc_html__('Core Widgets', 'pixel-gallery')
			],
			[
				'id' => 'pixel_gallery_elementor_extend',
				'title' => esc_html__('Extensions', 'pixel-gallery')
			],
		];

		return $sections;
	}

	/**
	 * Merge Admin Settings
	 *
	 * @access protected
	 * @return array
	 */

	protected function pixel_gallery_admin_settings()
	{

		return ModuleService::get_widget_settings(function ($settings) {
			$settings_fields = $settings['settings_fields'];

			self::$modules_list = $settings_fields['pixel_gallery_active_modules'];
			self::$modules_list_only_widgets = $settings_fields['pixel_gallery_active_modules'];

			return $settings_fields;
		});
	}

	/**
	 * Get Welcome Panel
	 *
	 * @access public
	 * @return void
	 */

	public function old_pixel_gallery_welcome()
	{
		?>

		<div class="pg-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-1-2@m bdt-width-1-4@l">
					<div class="pg-widget-status bdt-card bdt-card-body">

						<?php
						$used_widgets = count(self::get_used_widgets());
						$un_used_widgets = count(self::get_unused_widgets());
						?>
						<div class="pg-count-canvas-wrap">
							<h1 class="pg-feature-title"><?php echo esc_html__( 'All Widgets', 'pixel-gallery' ); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php echo esc_html__( 'Used:', 'pixel-gallery' ); ?>
										<b><?php echo esc_html( $used_widgets ); ?></b></div>
									<div class="pg-widget-count"><?php echo esc_html__( 'Unused:', 'pixel-gallery' ); ?>
										<b><?php echo esc_html( $un_used_widgets ); ?></b></div>
									<div class="pg-widget-count"><?php echo esc_html__( 'Total:', 'pixel-gallery' ); ?>
										<b><?php echo esc_html( $used_widgets + $un_used_widgets ); ?></b>
									</div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-db-total-status" style="height: 100px; width: 100px;"
										data-label="<?php /* translators: %s: total number of widgets */ echo esc_attr( sprintf( __( 'Total Widgets Status - (%s)', 'pixel-gallery' ), $used_widgets + $un_used_widgets ) ); ?>"
										data-labels="<?php echo esc_attr( sprintf( '%1$s, %2$s', __( 'Used', 'pixel-gallery' ), __( 'Unused', 'pixel-gallery' ) ) ); ?>"
										data-value="<?php echo esc_attr($used_widgets) . ',' . esc_attr($un_used_widgets); ?>"
										data-bg="#FFD166, #fff4d9" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="bdt-width-1-2@m bdt-width-1-4@l">
					<div class="pg-widget-status bdt-card bdt-card-body">

						<div class="pg-count-canvas-wrap">
							<h1 class="pg-feature-title"><?php esc_html_e( 'Active', 'pixel-gallery' ); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php esc_html_e('Core: ', 'pixel-gallery'); ?><b
											id="bdt-total-widgets-status-core"></b></div>
									<div class="pg-widget-count"><?php esc_html_e('Total:', 'pixel-gallery'); ?> <b
											id="bdt-total-widgets-status-heading"></b></div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-total-widgets-status" style="height: 100px; width: 100px;"
										data-labels="Total Active, Total Widgets" data-bg="#0680d6, #E6F9FF"
										data-bg-hover="#0673e1, #b6f9e8">
									</canvas>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="bdt-width-1-1@m bdt-width-1-2@l">
					<div class="pg-elementor-addons bdt-card bdt-card-body">
						<a target="_blank" rel="" href="https://www.elementpack.pro/elements-demo/"></a>
					</div>
				</div>

			</div>


			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-2-5@m pg-support-section">
					<div class="pg-support-content bdt-card bdt-card-body">
						<h1 class="pg-feature-title"><?php esc_html_e( 'Support And Feedback', 'pixel-gallery' ); ?></h1>
						<p>
							<?php
							printf(
								/* translators: %1$s: opening PixelGallery link tag, %2$s: closing link tag */
								wp_kses_post( __( 'Feeling like to consult with an expert? Take live chat support immediately from %1$sPixelGallery%2$s. We are always ready to help you 24/7.', 'pixel-gallery' ) ),
								'<a href="' . esc_url( 'https://pixelgallery.com' ) . '" target="_blank" rel="noopener noreferrer">',
								'</a>'
							);
							?>
						</p>
						<p><strong><?php esc_html_e( 'Or if you’re facing technical issues with our plugin, then please create a support ticket', 'pixel-gallery' ); ?></strong></p>
						<a class="bdt-button bdt-btn-blue bdt-margin-small-top bdt-margin-small-right" target="_blank" rel=""
							href="https://bdthemes.com/all-knowledge-base-of-pixel-gallery/">Knowledge
							Base</a>
						<a class="bdt-button bdt-btn-grey bdt-margin-small-top" target="_blank"
							href="https://bdthemes.com/support/">Get Support</a>
					</div>
				</div>

				<div class="bdt-width-3-5@m">
					<div class="bdt-card bdt-card-body pg-system-requirement">
						<h1 class="pg-feature-title bdt-margin-small-bottom"><?php esc_html_e( 'System Requirement', 'pixel-gallery' ); ?></h1>
						<?php $this->pixel_gallery_system_requirement(); ?>
					</div>
				</div>
			</div>

			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-1-2@m pg-support-section">
					<div class="bdt-card bdt-card-body pg-feedback-bg">
						<h1 class="pg-feature-title"><?php esc_html_e( 'Missing Any Feature?', 'pixel-gallery' ); ?></h1>
						<p style="max-width: 520px;"><?php esc_html_e( 'Are you in need of a feature that’s not available in our plugin? Feel free to do a feature request from here,', 'pixel-gallery' ); ?></p>
						<a class="bdt-button bdt-btn-yellow bdt-margin-small-top" target="_blank" rel=""
							href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/">Request Feature</a>
					</div>
				</div>

				<div class="bdt-width-1-2@m">
					<div class="bdt-card bdt-card-body pg-tryaddon-bg">
						<h1 class="pg-feature-title"><?php esc_html_e( 'Try Our Others Addons', 'pixel-gallery' ); ?></h1>
						<p style="max-width: 520px;">
							<b><?php esc_html_e( 'Element Pack, Prime Slider, Pixel Gallery & Ultimate Store Kit', 'pixel-gallery' ); ?></b> <?php esc_html_e( 'addons for', 'pixel-gallery' ); ?> <b><?php esc_html_e( 'Elementor', 'pixel-gallery' ); ?></b> <?php esc_html_e( 'is the best slider & blogs plugin for WordPress.', 'pixel-gallery' ); ?>
						</p>
						<div class="bdt-others-plugins-link">
							<a class="bdt-button bdt-btn-ep bdt-margin-small-right" target="_blank"
								href="https://wordpress.org/plugins/bdthemes-element-pack-lite/"
								bdt-tooltip="<?php echo esc_attr__( 'Element Pack Lite provides more than 50+ essential elements for everyday applications to simplify the whole web building process. It\'s Free! Download it.', 'pixel-gallery' ); ?>">Element
								pack</a>
							<a class="bdt-button bdt-btn-ps bdt-margin-small-right" target="_blank"
								href="https://wordpress.org/plugins/bdthemes-prime-slider-lite/"
								bdt-tooltip="<?php echo esc_attr__( 'The revolutionary slider builder addon for Elementor with next-gen superb interface. It\'s Free! Download it.', 'pixel-gallery' ); ?>">Prime
								Slider</a>
							<a class="bdt-button bdt-btn-pg bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/pixel-gallery/"
								bdt-tooltip="<?php echo esc_attr__( 'Best blogging addon for building quality blogging website with fine-tuned features and widgets. It\'s Free! Download it.', 'pixel-gallery' ); ?>">Pixel
								Gallery</a>
							<a class="bdt-button bdt-btn-usk bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/ultimate-store-kit/"
								bdt-tooltip="<?php echo esc_attr__( 'The only eCommerce addon for answering all your online store design problems in one package. It\'s Free! Download it.', 'pixel-gallery' ); ?>">Ultimate
								Store Kit</a>
							<a class="bdt-button bdt-btn-live-copy bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/live-copy-paste/"
								bdt-tooltip="<?php echo esc_attr__( 'Superfast cross-domain copy-paste mechanism for WordPress websites with true UI copy experience. It\'s Free! Download it.', 'pixel-gallery' ); ?>">Live
								Copy Paste</a>
						</div>

					</div>
				</div>
			</div>

		</div>


		<?php
	}

	/**
	 * Get Welcome Panel
	 *
	 * @access public
	 * @return void
	 */

	public function pixel_gallery_welcome()
	{

		?>

		<div class="pg-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

			<div class="pg-dashboard-welcome-container">

				<div class="pg-dashboard-item pg-dashboard-welcome bdt-card bdt-card-body">
					<h1 class="pg-feature-title pg-dashboard-welcome-title">
						<?php esc_html_e('Welcome to Pixel Gallery!', 'pixel-gallery'); ?>
					</h1>
					<p class="pg-dashboard-welcome-desc">
						<?php esc_html_e('Empower your web creation with powerful widgets, advanced extensions, ready templates and more.', 'pixel-gallery'); ?>
					</p>
					<a href="<?php echo esc_url( admin_url( '?pg_setup_wizard=show' ) ); ?>"
						class="bdt-button bdt-welcome-button bdt-margin-small-top"
						target="_blank"><?php esc_html_e('Setup Pixel Gallery', 'pixel-gallery'); ?></a>

					<div class="pg-dashboard-compare-section">
						<h4 class="pg-feature-sub-title">
							<?php /* translators: %1$s: opening highlight tag, %2$s: closing highlight tag */ printf(esc_html__('Unlock %1$sPremium Features%2$s', 'pixel-gallery'), '<strong class="pg-highlight-text">', '</strong>'); ?>
						</h4>
						<h1 class="pg-feature-title pg-dashboard-compare-title">
							<?php esc_html_e('Create Your Sleek Website with Pixel Gallery Pro!', 'pixel-gallery'); ?>
						</h1>
						<p><?php esc_html_e('Don\'t need more plugins. This pro addon helps you build complex or professional websites—visually stunning, functional and customizable.', 'pixel-gallery'); ?>
						</p>
						<ul>
							<li><?php esc_html_e('Entrance Animation', 'pixel-gallery'); ?></li>
							<li><?php esc_html_e('Custom CSS & JS', 'pixel-gallery'); ?></li>
							<li><?php esc_html_e('White Label Branding', 'pixel-gallery'); ?></li>
							<li><?php esc_html_e('Powerful Gallery Widgets and Advanced Extensions', 'pixel-gallery'); ?>
							</li>
						</ul>
						<div class="pg-dashboard-compare-section-buttons">
							<a href="https://pixelgallery.pro/pricing/" class="bdt-button bdt-welcome-button"
								target="_blank"><?php esc_html_e('Compare Free Vs Pro', 'pixel-gallery'); ?></a>
							<a href="https://store.bdthemes.com/pixel-gallery?utm_source=PixelGallery&utm_medium=PluginPage&utm_campaign=PixelGallery&coupon=FREETOPRO"
								class="bdt-button bdt-dashboard-sec-btn"
								target="_blank"><?php esc_html_e('Get Premium at 30% OFF', 'pixel-gallery'); ?></a>
						</div>
					</div>
				</div>

				<div class="pg-dashboard-item pg-dashboard-template-quick-access bdt-card bdt-card-body">
					<div class="pg-dashboard-template-section">
						<img src="<?php echo esc_url( BDTPG_ADMIN_URL . 'assets/images/template.jpg' ); ?>"
							alt="Pixel Gallery Dashboard Template">
						<h1 class="pg-feature-title ">
							<?php esc_html_e('Faster Web Creation with Sleek and Ready-to-Use Templates!', 'pixel-gallery'); ?>
						</h1>
						<p><?php esc_html_e('Build your wordpress websites of any niche—not from scratch and in a single click.', 'pixel-gallery'); ?>
						</p>
						<a href="https://pixelgallery.pro/" class="bdt-button bdt-dashboard-sec-btn bdt-margin-small-top"
							target="_blank"><?php esc_html_e('View Templates', 'pixel-gallery'); ?></a>
					</div>

					<div class="pg-dashboard-quick-access bdt-margin-medium-top">
						<img src="<?php echo esc_url( BDTPG_ADMIN_URL . 'assets/images/support.svg' ); ?>"
							alt="Pixel Gallery Dashboard Template">
						<h1 class="pg-feature-title">
							<?php esc_html_e('Getting Started with Quick Access', 'pixel-gallery'); ?>
						</h1>
						<ul>
							<li><a href="https://bdthemes.com/contact/"
									target="_blank"><?php esc_html_e('Contact Us', 'pixel-gallery'); ?></a></li>
							<li><a href="https://bdthemes.com/support/"
									target="_blank"><?php esc_html_e('Help Centre', 'pixel-gallery'); ?></a></li>
							<li><a href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/idea/new"
									target="_blank"><?php esc_html_e('Request a Feature', 'pixel-gallery'); ?></a>
							</li>
						</ul>
						<div class="pg-dashboard-support-section">
							<h1 class="pg-feature-title">
								<i class="dashicons dashicons-phone"></i>
								<?php esc_html_e('24/7 Support', 'pixel-gallery'); ?>
							</h1>
							<p><?php esc_html_e('Helping you get real-time solutions related to web creation with WordPress, Elementor, and Pixel Gallery.', 'pixel-gallery'); ?>
							</p>
							<a href="https://bdthemes.com/support/" class="bdt-margin-small-top"
								target="_blank"><?php esc_html_e('Get Your Support', 'pixel-gallery'); ?></a>
						</div>
					</div>
				</div>

				<div class="pg-dashboard-item pg-dashboard-request-feature bdt-card bdt-card-body">
					<h1 class="pg-feature-title pg-dashboard-template-quick-title">
						<?php esc_html_e('What\'s Stacking You?', 'pixel-gallery'); ?>
					</h1>
					<p><?php esc_html_e('We are always here to help you. If you have any feature request, please let us know.', 'pixel-gallery'); ?>
					</p>
					<a href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/idea/new"
						class="bdt-button bdt-dashboard-sec-btn bdt-margin-small-top"
						target="_blank"><?php esc_html_e('Request Your Features', 'pixel-gallery'); ?></a>
				</div>

				<a href="https://www.youtube.com/playlist?list=PLP0S85GEw7DPv5T-Ara11Zvplmk4ty0jy" target="_blank"
					class="pg-dashboard-item pg-dashboard-footer-item pg-dashboard-video-tutorial bdt-card bdt-card-body bdt-card-small">
					<span class="pg-dashboard-footer-item-icon">
						<i class="dashicons dashicons-video-alt3"></i>
					</span>
					<h1 class="pg-feature-title"><?php esc_html_e('Watch Video Tutorials', 'pixel-gallery'); ?></h1>
					<p><?php esc_html_e('An invaluable resource for mastering WordPress, Elementor, and Web Creation', 'pixel-gallery'); ?>
					</p>
				</a>
				<a href="https://bdthemes.com/knowledge-base/pixel-gallery/" target="_blank"
					class="pg-dashboard-item pg-dashboard-footer-item pg-dashboard-documentation bdt-card bdt-card-body bdt-card-small">
					<span class="pg-dashboard-footer-item-icon">
						<i class="dashicons dashicons-admin-tools"></i>
					</span>
					</span>
					<h1 class="pg-feature-title"><?php esc_html_e('Read Easy Documentation', 'pixel-gallery'); ?></h1>
					<p><?php esc_html_e('A way to eliminate the challenges you might face', 'pixel-gallery'); ?></p>
				</a>
				<a href="https://www.facebook.com/bdthemes" target="_blank"
					class="pg-dashboard-item pg-dashboard-footer-item pg-dashboard-community bdt-card bdt-card-body bdt-card-small">
					<span class="pg-dashboard-footer-item-icon">
						<i class="dashicons dashicons-admin-users"></i>
					</span>
					<h1 class="pg-feature-title"><?php esc_html_e('Join Our Community', 'pixel-gallery'); ?></h1>
					<p><?php esc_html_e('A platform for the opportunity to network, collaboration and innovation', 'pixel-gallery'); ?>
					</p>
				</a>
				<a href="https://wordpress.org/plugins/pixel-gallery/#reviews" target="_blank"
					class="pg-dashboard-item pg-dashboard-footer-item pg-dashboard-review bdt-card bdt-card-body bdt-card-small">
					<span class="pg-dashboard-footer-item-icon">
						<i class="dashicons dashicons-star-filled"></i>
					</span>
					<h1 class="pg-feature-title"><?php esc_html_e('Show Your Love', 'pixel-gallery'); ?></h1>
					<p><?php esc_html_e('A way of the assessment of code', 'pixel-gallery'); ?></p>
				</a>
			</div>

		</div>

		<?php
	}

	/**
	 * Get Pro
	 *
	 * @access public
	 * @return void
	 */

	function pixel_gallery_get_pro()
	{
		?>
		<div class="pg-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

			<div class="bdt-grid" bdt-grid bdt-height-match="target: > div > .bdt-card"
				style="max-width: 800px; margin-left: auto; margin-right: auto;">
				<div class="bdt-width-1-1@m pg-comparision bdt-text-center">
					<div class="bdt-flex bdt-flex-between bdt-flex-middle">
						<div class="bdt-text-left">
							<h1 class="bdt-text-bold"><?php esc_html_e( 'WHY GO WITH PRO?', 'pixel-gallery' ); ?></h1>
							<h2><?php esc_html_e( 'Just Compare With Pixel Gallery Free Vs Pro', 'pixel-gallery' ); ?></h2>
						</div>
						<?php if (true !== _is_pg_pro_activated()): ?>
							<div class="pg-purchase-button">
								<a href="<?php echo esc_url( 'https://pixelgallery.pro/pricing/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Purchase Now', 'pixel-gallery' ); ?></a>
							</div>
						<?php endif; ?>
					</div>

					<div>

						<ul class="bdt-list bdt-list-divider bdt-text-left bdt-text-normal" style="font-size: 15px;">


							<li class="bdt-text-bold">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m"><?php esc_html_e( 'Features', 'pixel-gallery' ); ?></div>
									<div class="bdt-width-auto@m"><?php esc_html_e( 'Free', 'pixel-gallery' ); ?></div>
									<div class="bdt-width-auto@m"><?php esc_html_e( 'Pro', 'pixel-gallery' ); ?></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">
										<span bdt-tooltip="<?php echo esc_attr( sprintf( 'pos: top-left; title: %s', __( 'Lite has 35+ widgets but Pro has 100+ core widgets', 'pixel-gallery' ) ) ); ?>">
											<?php esc_html_e( 'Core Widgets', 'pixel-gallery' ); ?>
										</span>
									</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Theme Compatibility</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Dynamic Content & Custom Fields Capabilities</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Proper Documentation</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Updates & Support</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Ready Made Pages</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Ready Made Blocks</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Elementor Extended Widgets</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Rooten Theme Pro Features</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-no"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Priority Support</div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-no"></span></div>
									<div class="bdt-width-auto@m"><span class="dashicons dashicons-yes"></span></div>
								</div>
							</li>

						</ul>


						<div class="pg-more-features bdt-card bdt-card-body bdt-margin-medium-top bdt-padding-large">
							<ul class="bdt-list bdt-list-divider bdt-text-left" style="font-size: 15px;">
								<li>
									<div class="bdt-grid bdt-grid-small">
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Incredibly Advanced
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Refund or Cancel Anytime
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Dynamic Content
										</div>
									</div>
								</li>

								<li>
									<div class="bdt-grid bdt-grid-small">
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Super-Flexible Widgets
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> 24/7 Premium Support
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Third Party Plugins
										</div>
									</div>
								</li>

								<li>
									<div class="bdt-grid bdt-grid-small">
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Special Discount!
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Custom Field Integration
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> With Live Chat Support
										</div>
									</div>
								</li>

								<li>
									<div class="bdt-grid bdt-grid-small">
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Trusted Payment Methods
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Interactive Effects
										</div>
										<div class="bdt-width-1-3@m">
											<span class="dashicons dashicons-heart"></span> Video Tutorial
										</div>
									</div>
								</li>
							</ul>

							<!-- <div class="pg-dashboard-divider"></div> -->

							<?php if (true !== _is_pg_pro_activated()): ?>
								<div class="pg-purchase-button bdt-margin-medium-top">
									<a href="<?php echo esc_url( 'https://pixelgallery.pro/pricing/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Purchase Now', 'pixel-gallery' ); ?></a>
								</div>
							<?php endif; ?>

						</div>

					</div>
				</div>
			</div>

		</div>
		<?php
	}


	/**
	 * Display Plugin Page
	 *
	 * @access public
	 * @return void
	 */

	public function plugin_page()
	{

		?>

		<div class="wrap pixel-gallery-dashboard">
			<h1></h1> <!-- don't remove this div, it's used for the notice container -->

			<div class="pg-dashboard-wrapper bdt-margin-top">
				<div class="pg-dashboard-header bdt-flex bdt-flex-wrap bdt-flex-between bdt-flex-middle"
					bdt-sticky="offset: 32; animation: bdt-animation-slide-top-small; duration: 300; media: 960">

					<div class="bdt-flex bdt-flex-wrap bdt-flex-middle">
						<!-- Header Shape Elements -->
						<div class="pg-header-elements">
							<span class="pg-header-element pg-header-circle"></span>
							<span class="pg-header-element pg-header-dots"></span>
							<span class="pg-header-element pg-header-line"></span>
							<span class="pg-header-element pg-header-square"></span>
							<span class="pg-header-element pg-header-wave"></span>
						</div>

						<div class="pg-logo">
							<?php
							$pg_default_logo = sprintf(
								'<img src="%1$s" alt="%2$s">',
								esc_url( BDTPG_URL . 'assets/images/logo-with-text.svg' ),
								esc_attr__( 'Pixel Gallery Logo', 'pixel-gallery' )
							);

							/**
							 * Filters the logo markup shown in the Pixel Gallery dashboard header.
							 *
							 * @param string $pg_default_logo Escaped default logo markup.
							 */
							echo wp_kses_post( apply_filters( 'pixel_gallery/admin/logo_html', $pg_default_logo ) );
							?>
						</div>
					</div>

					<div class="pg-dashboard-new-page-wrapper bdt-flex bdt-flex-wrap bdt-flex-middle">


						<!-- Always render save button, JavaScript will control visibility -->
						<div class="pg-dashboard-save-btn" style="display: none;">
							<button class="bdt-button bdt-button-primary pixel-gallery-settings-save-btn" type="submit">
								<?php esc_html_e('Save Settings', 'pixel-gallery'); ?>
							</button>
						</div>

						<?php
						/**
						 * Fires in the Pixel Gallery dashboard header action area.
						 *
						 * Allows add-ons to render their own header buttons next to
						 * the built-in "Save Settings" button.
						 */
						do_action( 'pixel_gallery/admin/header_actions' );
						?>

						<div class="pg-dashboard-new-page">
							<a class="bdt-flex bdt-flex-middle"
								href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" class=""><i
									class="dashicons dashicons-admin-page"></i>
								<?php echo esc_html__('Create New Page', 'pixel-gallery') ?>
							</a>
						</div>
					</div>
				</div>

				<div class="pg-dashboard-container bdt-flex">
					<div class="pg-dashboard-nav-container-wrapper">
						<div class="pg-dashboard-nav-container-inner"
							bdt-sticky="end: !.pg-dashboard-container; offset: 115; animation: bdt-animation-slide-top-small; duration: 300; media: 1200">

							<!-- Navigation Shape Elements -->
							<div class="pg-nav-elements">
								<span class="pg-nav-element pg-nav-circle"></span>
								<span class="pg-nav-element pg-nav-dots"></span>
								<span class="pg-nav-element pg-nav-line"></span>
								<span class="pg-nav-element pg-nav-square"></span>
								<span class="pg-nav-element pg-nav-triangle"></span>
								<span class="pg-nav-element pg-nav-plus"></span>
								<span class="pg-nav-element pg-nav-wave"></span>
							</div>

							<?php $this->settings_api->show_navigation(); ?>
						</div>
					</div>


					<div class="bdt-switcher bdt-tab-container bdt-container-xlarge bdt-flex-1">
						<div id="pixel_gallery_welcome_page" class="pg-option-page pg-group">
							<?php $this->pixel_gallery_welcome(); ?>
						</div>

						<?php $this->settings_api->show_forms(); ?>

						<div id="pixel_gallery_analytics_system_req_page" class="pg-option-page pg-group">
							<?php $this->pixel_gallery_analytics_system_req_content(); ?>
						</div>

						<div id="pixel_gallery_other_plugins_page" class="pg-option-page pg-group">
							<?php $this->pixel_gallery_others_plugin(); ?>
						</div>

						<!-- <div id="pixel_gallery_affiliate_page" class="pg-option-page pg-group">
							<?php //$this->pixel_gallery_affiliate_content(); ?>
						</div> -->

						<?php if (_is_pg_pro_activated() !== true): ?>
							<div id="pixel_gallery_get_pro" class="pg-option-page pg-group">
								<?php $this->pixel_gallery_get_pro(); ?>
							</div>
						<?php endif; ?>

						<?php
						/**
						 * Fires after the built-in Pixel Gallery dashboard tab panels.
						 *
						 * Add-ons that register extra sections through the
						 * `pixel_gallery/admin/settings_sections` filter should output their
						 * matching panel markup here, in the same order the sections were added.
						 */
						do_action( 'pixel_gallery/admin/settings_pages' );
						?>

						<?php if ($this->settings_api->has_section('pixel_gallery_license_settings')) : ?>
							<?php /* License is the last tab, so its panel is the last pane too. */ ?>
							<div id="pixel_gallery_license_settings_page" class="pg-option-page pg-group">

								<?php
								if (has_filter('pixel_gallery_license_page')) {
									apply_filters('pixel_gallery_license_page', '');
								} else {
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Deprecated alias; renaming would break released Pixel Gallery Pro versions.
									apply_filters('pg_license_page', '');
								}
								?>
							</div>
						<?php endif; ?>

					</div>
				</div>

				<?php $this->footer_info(); ?>
			</div>

		</div>

		<?php

		$this->script();

	}


	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	function script()
	{
		?>
		<script>
			jQuery(document).ready(function () {
				jQuery('.pg-no-result').removeClass('bdt-animation-shake');
			});

			function filterSearch(e) {
				var $parent = jQuery('#' + jQuery(e).data('id'));
				var search = String(jQuery(e).val() || '').toLowerCase().trim();
				var $items = $parent.find('.pg-options .pg-option-item');

				// UIkit's filter only reacts to clicks on links and buttons, so the
				// search shows and hides the widgets itself.
				$items.each(function () {
					var name = String(jQuery(this).attr('data-widget-name') || '').toLowerCase();
					jQuery(this).toggle(name.indexOf(search) > -1);
				});

				if (!search) {
					$parent.find('.pg-widget-all a').trigger('click');
				}

				$parent.find('.pg-no-result').toggleClass('bdt-animation-shake', '' !== search && 0 === $items.filter(':visible').length);
			}

			jQuery('.pg-options-parent').each(function (e, item) {
				var eachItem = '#' + jQuery(item).attr('id');
				jQuery(eachItem).on("beforeFilter", function () {
					jQuery(eachItem).find('.pg-no-result').removeClass('bdt-animation-shake');
				});

				jQuery(eachItem).on("afterFilter", function () {

					var isElementVisible = false;
					var i = 0;

					if (jQuery(eachItem).closest(".pg-options-parent").eq(i).is(":visible")) { } else {
						isElementVisible = true;
					}

					while (!isElementVisible && i < jQuery(eachItem).find(".pg-option-item").length) {
						if (jQuery(eachItem).find(".pg-option-item").eq(i).is(":visible")) {
							isElementVisible = true;
						}
						i++;
					}

					if (isElementVisible === false) {
						jQuery(eachItem).find('.pg-no-result').addClass('bdt-animation-shake');
					}
				});


			});


			jQuery('.pg-widget-filter-nav li a').on('click', function (e) {
				jQuery(this).closest('.bdt-widget-filter-wrapper').find('.bdt-search-input').val('');
				jQuery(this).closest('.bdt-widget-filter-wrapper').find('.bdt-search-input').val('').attr('bdt-filter-control', '');
			});


			jQuery(document).ready(function ($) {
				'use strict';

				function hashHandler() {
					var $tab = jQuery('.pixel-gallery-dashboard .bdt-tab');
					if (window.location.hash) {
						var hash = window.location.hash.substring(1);
						bdtUIkit.tab($tab).show(jQuery('#bdt-' + hash).data('tab-index'));

						// Update admin menu to match the active tab
						updateAdminMenuHighlight(hash);
					}
				}

				function updateAdminMenuHighlight(hash) {
					// Special case for Dashboard/Welcome tab
					if (hash === 'pixel_gallery_welcome' || !hash) {
						var dashboardMenuItem = jQuery('.toplevel_page_pixel_gallery_options > ul > li > a[href$="pixel_gallery_options"]').parent();
						dashboardMenuItem.siblings().removeClass('current');
						dashboardMenuItem.addClass('current');
					} else {
						// Update the corresponding admin menu item
						var adminMenuItem = jQuery('.toplevel_page_pixel_gallery_options > ul > li > a[href*="' + hash + '"]');
						if (adminMenuItem.length) {
							adminMenuItem.parent().siblings().removeClass('current');
							adminMenuItem.parent().addClass('current');
						}
					}
				}

				function onWindowLoad() {
					hashHandler();
				}

				if (document.readyState === 'complete') {
					onWindowLoad();
				} else {
					jQuery(window).on('load', onWindowLoad);
				}

				window.addEventListener("hashchange", hashHandler, true);

				jQuery('.toplevel_page_pixel_gallery_options > ul > li > a ').on('click', function (event) {
					jQuery(this).parent().siblings().removeClass('current');
					jQuery(this).parent().addClass('current');
				});

				// Handle navigation tab clicks to sync with admin menu
				jQuery('.bdt-dashboard-navigation a').on('click', function (e) {
					var href = jQuery(this).attr('href');
					if (href && href.startsWith('#')) {
						var hash = href.substring(1);
						updateAdminMenuHighlight(hash);
					}
				});

				jQuery('#pixel_gallery_active_modules_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .pg-checkbox:visible').each(function () {
						jQuery(this).attr('checked', 'checked').prop("checked", true);
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_active_modules_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();
					jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .pg-checkbox:visible').each(function () {
						jQuery(this).removeAttr('checked');
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-active-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					jQuery('#pixel_gallery_elementor_extend_page .pg-checkbox:visible').each(function () {
						jQuery(this).attr('checked', 'checked').prop("checked", true);
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_elementor_extend_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();
					jQuery('#pixel_gallery_elementor_extend_page .pg-checkbox:visible').each(function () {
						jQuery(this).removeAttr('checked');
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-active-all-widget').removeClass('bdt-active');
				});

				// Activate/Deactivate all widgets functionality
				$('#pixel_gallery_active_modules_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .pg-checkbox:visible').each(function () {
						$(this).attr('checked', 'checked').prop("checked", true);
					});

					$(this).addClass('bdt-active');
					$('#pixel_gallery_active_modules_page a.pg-deactive-all-widget').removeClass('bdt-active');

					// Ensure save button remains visible
					setTimeout(function () {
						$('.pg-dashboard-save-btn').show();
					}, 100);
				});

				$('#pixel_gallery_active_modules_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#pixel_gallery_active_modules_page .pg-checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#pixel_gallery_active_modules_page a.pg-active-all-widget').removeClass('bdt-active');

					// Ensure save button remains visible
					setTimeout(function () {
						$('.pg-dashboard-save-btn').show();
					}, 100);
				});

				$('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#pixel_gallery_elementor_extend_page .pg-option-item:not(.pg-pro-inactive) .pg-checkbox:visible').each(function () {
						$(this).attr('checked', 'checked').prop("checked", true);
					});

					$(this).addClass('bdt-active');
					$('#pixel_gallery_elementor_extend_page a.pg-deactive-all-widget').removeClass('bdt-active');

					// Ensure save button remains visible
					setTimeout(function () {
						$('.pg-dashboard-save-btn').show();
					}, 100);
				});

				$('#pixel_gallery_elementor_extend_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#pixel_gallery_elementor_extend_page .pg-checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').removeClass('bdt-active');

					// Ensure save button remains visible
					setTimeout(function () {
						$('.pg-dashboard-save-btn').show();
					}, 100);
				});

				jQuery('#pixel_gallery_active_modules_page .pg-pro-inactive .pg-checkbox').each(function () {
					jQuery(this).removeAttr('checked');
					jQuery(this).attr("disabled", true);
				});

			});

			jQuery(document).ready(function ($) {
				const getProLink = $('a[href="admin.php?page=pixel_gallery_options_get_pro"]');
				if (getProLink.length) {
					getProLink.attr('target', '_blank');
				}
			});

			// License Renew Redirect
			jQuery(document).ready(function ($) {
				const renewalLink = $('a[href="admin.php?page=pixel_gallery_options_license_renew"]');
				if (renewalLink.length) {
					renewalLink.attr('target', '_blank');
				}
			});

			// Dynamic Save Button Control
			jQuery(document).ready(function ($) {
				// Define pages that need save button - only specific settings pages
				const pagesWithSave = [
					'pixel_gallery_active_modules',        // Core widgets
					'pixel_gallery_elementor_extend',      // Extensions
					'pixel_gallery_api_settings'           // API settings
				];

				function toggleSaveButton() {
					const currentHash = window.location.hash.substring(1);
					const saveButton = $('.pg-dashboard-save-btn');

					// Check if current page should have save button
					if (pagesWithSave.includes(currentHash)) {
						saveButton.fadeIn(200);
					} else {
						saveButton.fadeOut(200);
					}
				}

				// Force save button to be visible for settings pages
				function forceSaveButtonVisible() {
					const currentHash = window.location.hash.substring(1);
					const saveButton = $('.pg-dashboard-save-btn');

					if (pagesWithSave.includes(currentHash)) {
						saveButton.show();
					}
				}

				// Initial check
				toggleSaveButton();

				// Listen for hash changes
				$(window).on('hashchange', function () {
					toggleSaveButton();
				});

				// Listen for tab clicks
				$('.bdt-dashboard-navigation a').on('click', function () {
					setTimeout(toggleSaveButton, 100);
				});

				// Also listen for navigation menu clicks (from show_navigation())
				$(document).on('click', '.bdt-tab a, .bdt-subnav a, .pg-dashboard-nav a, [href*="#pixel_gallery"]', function () {
					setTimeout(toggleSaveButton, 100);
				});

				// Listen for bulk active/deactive button clicks to maintain save button visibility
				$(document).on('click', '.pg-active-all-widget, .pg-deactive-all-widget', function () {
					setTimeout(forceSaveButtonVisible, 50);
				});

				// Listen for individual checkbox changes to maintain save button visibility
				$(document).on('change', '#pixel_gallery_elementor_extend_page .pg-checkbox, #pixel_gallery_active_modules_page .pg-checkbox', function () {
					setTimeout(forceSaveButtonVisible, 50);
				});

				// Update URL when navigation items are clicked
				$(document).on('click', '.bdt-tab a, .bdt-subnav a, .pg-dashboard-nav a', function (e) {
					const href = $(this).attr('href');
					if (href && href.includes('#')) {
						const hash = href.substring(href.indexOf('#'));
						if (hash && hash.length > 1) {
							// Update browser URL with the hash
							const currentUrl = window.location.href.split('#')[0];
							const newUrl = currentUrl + hash;
							window.history.pushState(null, null, newUrl);

							// Trigger hash change event for other listeners
							$(window).trigger('hashchange');
						}
					}
				});

				// Handle save button click
				$(document).on('click', '.pixel-gallery-settings-save-btn', function (e) {
					e.preventDefault();

					// Find the active form in the current tab
					const currentHash = window.location.hash.substring(1);
					let targetForm = null;

					// Look for forms in the active tab content
					if (currentHash) {
						// Try to find form in the specific tab page
						targetForm = $('#' + currentHash + '_page form.pg-settings-save');

						// If not found, try without _page suffix
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + ' form.pg-settings-save');
						}

						// Try to find any form in the active tab content
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + '_page form');
						}
					}

					// Fallback to any visible form with pg-settings-save class
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('form.pg-settings-save:visible').first();
					}

					// Last fallback - any visible form
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('.bdt-switcher .pg-option-page:visible form').first();
					}

					if (targetForm && targetForm.length > 0) {
						// Show loading notification
						// bdtUIkit.notification({
						// 	message: '<div bdt-spinner></div> <?php //esc_html_e('Please wait, Saving settings...', 'pixel-gallery') ?>',
						// 	timeout: false
						// });

						// Submit form using AJAX (same logic as existing form submission)
						targetForm.ajaxSubmit({
							success: function () {
								// Show success message using UIkit notification (same as main settings)
								bdtUIkit.notification.closeAll();
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Settings Saved Successfully.', 'pixel-gallery') ?>',
									status: 'primary',
									pos: 'top-center'
								});
							},
							error: function (data) {
								bdtUIkit.notification.closeAll();
								bdtUIkit.notification({
									message: '<span bdt-icon=\'icon: warning\'></span> <?php esc_html_e('Unknown error, make sure access is correct!', 'pixel-gallery') ?>',
									status: 'warning'
								});
							}
						});
					} else {
						// Show error if no form found
						bdtUIkit.notification({
							message: '<span bdt-icon="icon: warning"></span> <?php esc_html_e('No settings form found to save.', 'pixel-gallery') ?>',
							status: 'warning'
						});
					}
				});

			});

			// Chart.js initialization for system status canvas charts
			function initPixelGalleryCharts() {
				// Wait for Chart.js to be available
				if (typeof Chart === 'undefined') {
					setTimeout(initPixelGalleryCharts, 500);
					return;
				}

				// Chart instances storage
				window.pgChartInstances = window.pgChartInstances || {};
				window.pgChartsInitialized = false;

				// Function to create a chart
				function createChart(canvasId) {
					var canvas = document.getElementById(canvasId);
					if (!canvas) {
						return;
					}

					var $canvas = jQuery('#' + canvasId);
					var valueStr = $canvas.data('value');
					var labelsStr = $canvas.data('labels');
					var bgStr = $canvas.data('bg');

					if (!valueStr || !labelsStr || !bgStr) {
						return;
					}

					// Parse data
					var values = valueStr.toString().split(',').map(v => parseInt(v.trim()) || 0);
					var labels = labelsStr.toString().split(',').map(l => l.trim());
					var colors = bgStr.toString().split(',').map(c => c.trim());

					// Destroy existing chart using Chart.js built-in method
					var existingChart = Chart.getChart(canvas);
					if (existingChart) {
						existingChart.destroy();
					}

					// Also destroy from our instance storage
					if (window.pgChartInstances && window.pgChartInstances[canvasId]) {
						window.pgChartInstances[canvasId].destroy();
						delete window.pgChartInstances[canvasId];
					}

					// Create new chart
					try {
						var newChart = new Chart(canvas, {
							type: 'doughnut',
							data: {
								labels: labels,
								datasets: [{
									data: values,
									backgroundColor: colors,
									borderWidth: 0
								}]
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
								plugins: {
									legend: { display: false },
									tooltip: { enabled: true }
								},
								cutout: '60%'
							}
						});

						// Store in our instance storage
						if (!window.pgChartInstances) window.pgChartInstances = {};
						window.pgChartInstances[canvasId] = newChart;
					} catch (error) {
						// Do nothing
					}
				}

				// Update total widgets status
				function updateTotalStatus() {
					var coreCount = jQuery('#pixel_gallery_active_modules_page input:checked').length;
					var extensionsCount = jQuery('#pixel_gallery_elementor_extend_page input:checked').length;

					jQuery('#bdt-total-widgets-status-core').text(coreCount);
					jQuery('#bdt-total-widgets-status-extensions').text(extensionsCount);
					jQuery('#bdt-total-widgets-status-heading').text(coreCount + extensionsCount);

					jQuery('#bdt-total-widgets-status').attr('data-value', [coreCount, extensionsCount].join(','));
				}

				// Initialize all charts once
				function initAllCharts() {
					// Check if charts already exist and are properly rendered
					if (window.pgChartInstances && Object.keys(window.pgChartInstances).length >= 4) {
						return;
					}

					// Update total status first
					updateTotalStatus();

					// Create all charts
					var chartCanvases = [
						'bdt-db-total-status',
						'bdt-db-only-widget-status',
						'bdt-total-widgets-status'
					];

					var successfulCharts = 0;
					chartCanvases.forEach(function (canvasId) {
						var canvas = document.getElementById(canvasId);
						if (canvas && canvas.offsetParent !== null) { // Check if canvas is visible
							createChart(canvasId);
							if (window.pgChartInstances && window.pgChartInstances[canvasId]) {
								successfulCharts++;
							}
						}
					});
				}

				// Charts can only be measured once the System Status tab is on screen,
				// so wait until it is visible before drawing them.
				function renderChartsWhenVisible(attempt) {
					var pane = document.getElementById('pixel_gallery_analytics_system_req_page');
					attempt = attempt || 0;

					if (!pane) {
						return;
					}

					if (pane.offsetParent === null) {
						if (attempt < 40) {
							setTimeout(function () {
								renderChartsWhenVisible(attempt + 1);
							}, 100);
						}
						return;
					}

					initAllCharts();
				}

				function renderChartsIfOnSystemStatus() {
					if (window.location.hash === '#pixel_gallery_analytics_system_req') {
						renderChartsWhenVisible();
					}
				}

				// Covers loading the page on the tab, the admin menu links and back/forward.
				renderChartsIfOnSystemStatus();
				jQuery(window).on('hashchange', renderChartsIfOnSystemStatus);

				jQuery(document).on('click', 'a[href="#pixel_gallery_analytics_system_req"], a[href*="pixel_gallery_analytics_system_req"]', function () {
					renderChartsWhenVisible();
				});
			}

			// Start the chart initialization
			setTimeout(initPixelGalleryCharts, 1000);

			// Handle plugin installation via AJAX
			jQuery(document).on('click', '.pg-install-plugin', function (e) {
				e.preventDefault();

				var $button = jQuery(this);
				var pluginSlug = $button.data('plugin-slug');
				var nonce = $button.data('nonce');
				var originalText = $button.text();

				// Disable button and show loading state
				$button.prop('disabled', true)
					.text('<?php echo esc_js(__('Installing...', 'pixel-gallery')); ?>')
					.addClass('bdt-installing');

				// Perform AJAX request
				jQuery.ajax({
					url: '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>',
					type: 'POST',
					data: {
						action: 'bdtpg_install_plugin',
						plugin_slug: pluginSlug,
						nonce: nonce
					},
					success: function (response) {
						if (response.success) {
							// Show success message
							$button.text('<?php echo esc_js(__('Installed!', 'pixel-gallery')); ?>')
								.removeClass('bdt-installing')
								.addClass('bdt-installed');

							// Show success notification
							if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-yes"></span> ' + response.data.message,
									status: 'success'
								});
							}

							// Reload the page after 2 seconds to update button states
							setTimeout(function () {
								window.location.reload();
							}, 2000);

						} else {
							// Show error message
							$button.prop('disabled', false)
								.text(originalText)
								.removeClass('bdt-installing');

							// Show error notification
							if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
								bdtUIkit.notification({
									message: '<span class="dashicons dashicons-warning"></span> ' + response.data.message,
									status: 'danger'
								});
							}
						}
					},
					error: function () {
						// Handle network/server errors
						$button.prop('disabled', false)
							.text(originalText)
							.removeClass('bdt-installing');

						// Show error notification
						if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
							bdtUIkit.notification({
								message: '<span class="dashicons dashicons-warning"></span> <?php echo esc_js(__('Installation failed. Please try again.', 'pixel-gallery')); ?>',
								status: 'danger'
							});
						}
					}
				});
			});

		</script>
		<?php
	}

	/**
	 * Display Footer
	 *
	 * @access public
	 * @return void
	 */

	function footer_info()
	{
		?>

		<div class="pixel-gallery-footer-info bdt-margin-medium-top">

			<div class="bdt-grid ">

				<div class="bdt-width-auto@s pg-setting-save-btn">



				</div>

				<div class="bdt-width-expand@s bdt-text-right">
					<p class="">
						<?php
						printf(
							/* translators: %1$s: opening BdThemes link tag, %2$s: closing link tag */
							wp_kses_post( __( 'Pixel Gallery plugin made with love by %1$sBdThemes%2$s Team.', 'pixel-gallery' ) ),
							'<a target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://bdthemes.com' ) . '">',
							'</a>'
						);
						?>
						<br>
						<?php
						printf(
							/* translators: %1$s: opening BdThemes link tag, %2$s: closing link tag */
							wp_kses_post( __( 'All rights reserved by %1$sBdThemes.com%2$s.', 'pixel-gallery' ) ),
							'<a target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://bdthemes.com' ) . '">',
							'</a>'
						);
						?>
					</p>
				</div>
			</div>

		</div>

		<?php
	}

	/**
	 *
	 * Allow Tracker deactivated warning
	 * If Allow Tracker disable in elementor then this biggopti will be show
	 *
	 * @access public
	 */

	public function allow_tracker_activate_biggopti()
	{
		Biggopties::add_biggopti(
			[
				'id' => 'pg-allow-tracker',
				'type' => 'warning',
				'category' => 'critical',
				'dismissible' => true,
				'dismissible-time' => WEEK_IN_SECONDS * 4,
				'message' => __('Please activate <strong>Usage Data Sharing</strong> features from Elementor, otherwise Widgets Analytics will not work. Please activate the settings from <strong>Elementor > Settings > General Tab >  Usage Data Sharing.</strong> Thank you.', 'pixel-gallery'),
			]
		);
	}

	/**
	 * Widgets Status
	 */

	public function pixel_gallery_widgets_status()
	{
		$track_nw_msg = '';
		if (!Tracker::is_allow_track()) {
			$track_nw = esc_html__('This feature is not working because the Elementor Usage Data Sharing feature is Not Enabled.', 'pixel-gallery');
			$track_nw_msg = 'bdt-tooltip="' . $track_nw . '"';
		}
		?>
		<div class="pg-dashboard-widgets-status">
			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-1-2@m bdt-width-1-3@xl">
					<div class="pg-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<?php
						$used_widgets = count(self::get_used_widgets());
						$un_used_widgets = count(self::get_unused_widgets());
						?>

						<div class="pg-count-canvas-wrap">
							<h1 class="pg-feature-title"><?php esc_html_e('All Widgets', 'pixel-gallery'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php esc_html_e('Used:', 'pixel-gallery'); ?> <b>
											<?php echo esc_html($used_widgets); ?>
										</b></div>
									<div class="pg-widget-count"><?php esc_html_e('Unused:', 'pixel-gallery'); ?> <b>
											<?php echo esc_html($un_used_widgets); ?>
										</b>
									</div>
									<div class="pg-widget-count"><?php esc_html_e('Total:', 'pixel-gallery'); ?>
										<b>
											<?php echo esc_html($used_widgets + $un_used_widgets); ?>
										</b>
									</div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-db-total-status" style="height: 100px; width: 100px;"
										data-label="Total Widgets Status - (<?php echo esc_html($used_widgets + $un_used_widgets); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
										data-value="<?php echo esc_attr($used_widgets) . ',' . esc_attr($un_used_widgets); ?>"
										data-bg="#FFD166, #fff4d9" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>
				<div class="bdt-width-1-2@m bdt-width-1-3@xl">
					<div class="pg-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<?php
						$used_only_widgets = count(self::get_used_only_widgets());
						$unused_only_widgets = count(self::get_unused_only_widgets());
						?>


						<div class="pg-count-canvas-wrap">
							<h1 class="pg-feature-title"><?php esc_html_e('Core', 'pixel-gallery'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php esc_html_e('Used:', 'pixel-gallery'); ?> <b>
											<?php echo esc_html($used_only_widgets); ?>
										</b></div>
									<div class="pg-widget-count"><?php esc_html_e('Unused:', 'pixel-gallery'); ?> <b>
											<?php echo esc_html($unused_only_widgets); ?>
										</b></div>
									<div class="pg-widget-count"><?php esc_html_e('Total:', 'pixel-gallery'); ?>
										<b>
											<?php echo esc_html($used_only_widgets + $unused_only_widgets); ?>
										</b>
									</div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-db-only-widget-status" style="height: 100px; width: 100px;"
										data-label="Core Widgets Status - (<?php echo esc_html($used_only_widgets + $unused_only_widgets); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
										data-value="<?php echo esc_attr($used_only_widgets) . ',' . esc_attr($unused_only_widgets); ?>"
										data-bg="#EF476F, #ffcdd9" data-bg-hover="#0673e1, #e71522"></canvas>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="bdt-width-1-2@m bdt-width-1-3@xl">
					<div class="pg-widget-status bdt-card bdt-card-body" <?php echo wp_kses_post($track_nw_msg); ?>>

						<div class="pg-count-canvas-wrap">
							<h1 class="pg-feature-title"><?php esc_html_e('Active', 'pixel-gallery'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php esc_html_e('Core:', 'pixel-gallery'); ?>
										<b id="bdt-total-widgets-status-core">0</b>
									</div>
									<div class="pg-widget-count"><?php esc_html_e('Extensions:', 'pixel-gallery'); ?>
										<b id="bdt-total-widgets-status-extensions">0</b>
									</div>
									<div class="pg-widget-count"><?php esc_html_e('Total:', 'pixel-gallery'); ?> <b
											id="bdt-total-widgets-status-heading">0</b></div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-total-widgets-status" style="height: 100px; width: 100px;"
										data-label="Total Active Widgets Status"
										data-labels="<?php echo esc_attr('Core, Extensions'); ?>" data-value="0,0,0"
										data-bg="#0680d6, #B0EBFF" data-bg-hover="#0673e1, #B0EBFF">
									</canvas>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		<?php if (!Tracker::is_allow_track()): ?>
			<div class="bdt-border-rounded bdt-box-shadow-small bdt-alert-warning" bdt-alert>
				<a href class="bdt-alert-close" bdt-close></a>
				<div class="bdt-text-default">
					<?php
					printf(
						/* translators: %1$s: opening bold tag, %2$s: closing bold tag */
						esc_html__('To view widgets analytics, Elementor %1$sUsage Data Sharing%2$s feature by Elementor needs to be activated. Please activate the feature to get widget analytics instantly ', 'pixel-gallery'),
						'<b>',
						'</b>'
					);

					echo ' <a href="' . esc_url(admin_url('admin.php?page=elementor-settings')) . '">' . esc_html__('from here.', 'pixel-gallery') . '</a>';
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php
	}

	/**
	 * Display System Requirement
	 *
	 * @access public
	 * @return void
	 */

	public function pixel_gallery_system_requirement()
	{
		$php_version = phpversion();
		$max_execution_time = ini_get('max_execution_time');
		$memory_limit = ini_get('memory_limit');
		$post_limit = ini_get('post_max_size');
		$uploads = wp_upload_dir();
		$upload_path = $uploads['basedir'];
		$yes_icon = '<span class="pg-valid"><i class="dashicons-before dashicons-yes"></i></span>';
		$no_icon = '<span class="pg-invalid"><i class="dashicons-before dashicons-no-alt"></i></span>';

		$environment = Utils::get_environment_info();

		?>
		<ul class="pg-check-system-status bdt-grid bdt-child-width-1-2@m  bdt-grid-small ">
			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('PHP Version:', 'pixel-gallery'); ?></span>

					<?php
					if (version_compare($php_version, '7.4.0', '<')) {
						echo wp_kses_post($no_icon);
						echo '<span class="pg-label2" title="' . esc_attr__('Min: 7.4 Recommended', 'pixel-gallery') . '" bdt-tooltip>' . esc_html__('Currently:', 'pixel-gallery') . ' ' . esc_html($php_version) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__('Currently:', 'pixel-gallery') . ' ' . esc_html($php_version) . '</span>';
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('Max execution time:', 'pixel-gallery'); ?> </span>
					<?php
					if ($max_execution_time < '90') {
						echo wp_kses_post($no_icon);
						echo '<span class="pg-label2" title="' . esc_attr__( 'Min: 90 Recommended', 'pixel-gallery' ) . '" bdt-tooltip>' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $max_execution_time ) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $max_execution_time ) . '</span>';
					}
					?>
				</div>
			</li>
			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('Memory Limit:', 'pixel-gallery'); ?> </span>

					<?php
					if (intval($memory_limit) < '512') {
						echo wp_kses_post($no_icon);
						echo '<span class="pg-label2" title="' . esc_attr__( 'Min: 512M Recommended', 'pixel-gallery' ) . '" bdt-tooltip>' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $memory_limit ) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $memory_limit ) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('Max Post Limit:', 'pixel-gallery'); ?> </span>

					<?php
					if (intval($post_limit) < '32') {
						echo wp_kses_post($no_icon);
						echo '<span class="pg-label2" title="' . esc_attr__( 'Min: 32M Recommended', 'pixel-gallery' ) . '" bdt-tooltip>' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $post_limit ) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__( 'Currently:', 'pixel-gallery' ) . ' ' . esc_html( $post_limit ) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('Uploads folder writable:', 'pixel-gallery'); ?></span>

					<?php
					if (!wp_is_writable($upload_path)) {
						echo wp_kses_post($no_icon);
					} else {
						echo wp_kses_post($yes_icon);
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('MultiSite:', 'pixel-gallery'); ?></span>

					<?php
					if ($environment['wp_multisite']) {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__('MultiSite Enabled', 'pixel-gallery') . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__('Single Site', 'pixel-gallery') . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('GZip Enabled:', 'pixel-gallery'); ?></span>

					<?php
					if ($environment['gzip_enabled']) {
						echo wp_kses_post($yes_icon);
					} else {
						echo wp_kses_post($no_icon);
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="pg-label1"><?php esc_html_e('Debug Mode:', 'pixel-gallery'); ?></span>
					<?php
					if ($environment['wp_debug_mode']) {
						echo wp_kses_post($no_icon);
						echo '<span class="pg-label2">' . esc_html__('Currently Turned On', 'pixel-gallery') . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="pg-label2">' . esc_html__('Currently Turned Off', 'pixel-gallery') . '</span>';
					}
					?>
				</div>

			</li>

		</ul>

		<div class="bdt-admin-alert">
			<strong><?php esc_html_e('Note:', 'pixel-gallery'); ?></strong>
			<?php
			printf(
				/* translators: %s: Plugin name 'Pixel Gallery' */
				esc_html__('If you have multiple addons like %s so you may need to allocate additional memory for other addons as well.', 'pixel-gallery'),
				'<b>Pixel Gallery</b>'
			);
			?>
		</div>

		<?php
	}

	/**
	 * Display Analytics and System Requirements
	 *
	 * @access public
	 * @return void
	 */

	public function pixel_gallery_analytics_system_req_content()
	{
		?>
		<div class="pg-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="pg-dashboard-analytics-system">

				<?php $this->pixel_gallery_widgets_status(); ?>

				<div class="bdt-grid bdt-grid-medium bdt-margin-medium-top" bdt-grid
					bdt-height-match="target: > div > .bdt-card">
					<div class="bdt-width-1-1">
						<div class="bdt-card bdt-card-body pg-system-requirement">
							<h1 class="pg-feature-title bdt-margin-small-bottom">
								<?php esc_html_e('System Requirement', 'pixel-gallery'); ?>
							</h1>
							<?php $this->pixel_gallery_system_requirement(); ?>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Get License Email
	 *
	 * @access public
	 * @return string
	 */

	public static function get_license_email()
	{
		return trim(get_option('pixel_gallery_license_email', get_bloginfo('admin_email')));
	}

	/**
	 * Others Plugin - Using standalone plugin manager
	 */
	public function pixel_gallery_others_plugin() {
		// Include and render the standalone others plugin manager
		require_once BDTPG_INC_PATH . 'setup-wizard/pixel-gallery-others-plugin.php';
		
		// Call the helper function to render the plugin manager
		pixel_gallery_others_plugin();
	}

	/**
	 * Check plugin status (installed, active, or not installed)
	 * 
	 * @param string $plugin_path Plugin file path
	 * @return string 'active', 'installed', or 'not_installed'
	 */
	private function get_plugin_status($plugin_path)
	{
		// Check if plugin is active
		if (is_plugin_active($plugin_path)) {
			return 'active';
		}

		// Check if plugin is installed but not active
		$installed_plugins = get_plugins();
		if (isset($installed_plugins[$plugin_path])) {
			return 'installed';
		}

		// Plugin is not installed
		return 'not_installed';
	}

	/**
	 * Handle AJAX plugin installation
	 * 
	 * @access public
	 * @return void
	 */
	public function install_plugin_ajax()
	{
		// Check nonce
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

		if (!wp_verify_nonce($nonce, 'bdtpg_install_plugin_nonce')) {
			wp_send_json_error(['message' => __('Security check failed', 'pixel-gallery')]);
		}

		// Check user capability
		if (!current_user_can('install_plugins')) {
			wp_send_json_error(['message' => __('You do not have permission to install plugins', 'pixel-gallery')]);
		}

		$plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';

		if (empty($plugin_slug)) {
			wp_send_json_error(['message' => __('Plugin slug is required', 'pixel-gallery')]);
		}

		// Include necessary WordPress files
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

		// Get plugin information
		$api = plugins_api('plugin_information', [
			'slug' => $plugin_slug,
			'fields' => [
				'sections' => false,
			],
		]);

		if (is_wp_error($api)) {
			wp_send_json_error(['message' => __('Plugin not found: ', 'pixel-gallery') . $api->get_error_message()]);
		}

		// Install the plugin
		$skin = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader($skin);
		$result = $upgrader->install($api->download_link);

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => __('Installation failed: ', 'pixel-gallery') . $result->get_error_message()]);
		} elseif ($skin->get_errors()->has_errors()) {
			wp_send_json_error(['message' => __('Installation failed: ', 'pixel-gallery') . $skin->get_error_messages()]);
		} elseif (is_null($result)) {
			wp_send_json_error(['message' => __('Installation failed: Unable to connect to filesystem', 'pixel-gallery')]);
		}

		// Get installation status
		$install_status = install_plugin_install_status($api);

		wp_send_json_success([
			'message' => __('Plugin installed successfully!', 'pixel-gallery'),
			'plugin_file' => $install_status['file'],
			'plugin_name' => $api->name
		]);
	}

	/**
	 * Extract plugin slug from plugin path
	 * 
	 * @param string $plugin_path Plugin file path
	 * @return string Plugin slug
	 */
	private function extract_plugin_slug_from_path($plugin_path)
	{
		$parts = explode('/', $plugin_path);
		return isset($parts[0]) ? $parts[0] : '';
	}

	/**
	 * Get plugin action button HTML based on plugin status
	 * 
	 * @param string $plugin_path Plugin file path
	 * @param string $install_url Plugin installation URL
	 * @param string $plugin_slug Plugin slug for activation
	 * @return string Button HTML
	 */
	private function get_plugin_action_button($plugin_path, $install_url, $plugin_slug = '')
	{
		$status = $this->get_plugin_status($plugin_path);

		switch ($status) {
			case 'active':
				return '';

			case 'installed':
				$activate_url = wp_nonce_url(
					add_query_arg([
						'action' => 'activate',
						'plugin' => $plugin_path
					], admin_url('plugins.php')),
					'activate-plugin_' . $plugin_path
				);
				return '<a class="bdt-button bdt-welcome-button" href="' . esc_url($activate_url) . '">' .
					__('Activate', 'pixel-gallery') . '</a>';

			case 'not_installed':
			default:
				$plugin_slug = $this->extract_plugin_slug_from_path($plugin_path);
				$nonce = wp_create_nonce('bdtpg_install_plugin_nonce');
				return '<a class="bdt-button bdt-welcome-button pg-install-plugin" 
				          data-plugin-slug="' . esc_attr($plugin_slug) . '" 
				          data-nonce="' . esc_attr($nonce) . '" 
				          href="#">' .
					__('Install', 'pixel-gallery') . '</a>';
		}
	}

	/**
	 * Get License Key
	 *
	 * @access public
	 * @return string
	 */

	public static function get_license_key()
	{
		$license_key = get_option('pixel_gallery_license_key');
		return trim($license_key);
	}

}

new PixelGallery_Admin_Settings();
