<?php

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

	/**
	 * Rollback version instance
	 * 
	 * @var Rollback_Version
	 */
	public $rollback_version;

	function __construct()
	{
		$this->settings_api = new PixelGallery_Settings_API;

		if (!defined('BDTPG_HIDE')) {
			add_action('admin_init', [$this, 'admin_init']);
			add_action('admin_menu', [$this, 'admin_menu'], 201);
		}

		if (!Tracker::is_allow_track()) {
			add_action('admin_notices', [$this, 'allow_tracker_activate_biggopti'], 10, 3);
		}

		// Handle white label access link
		$this->handle_white_label_access();

		// Add custom CSS/JS functionality
		$this->init_custom_code_functionality();

		// White label settings (admin only)
		add_action('wp_ajax_pg_save_white_label', [$this, 'save_white_label_ajax']);
		add_action('wp_ajax_pg_revoke_white_label_token', [$this, 'revoke_white_label_token_ajax']);
		add_action('admin_head', [$this, 'inject_white_label_icon_css']);

		// Plugin installation (admin only)
		add_action('wp_ajax_pg_install_plugin', [$this, 'install_plugin_ajax']);



		if (_is_pg_pro_activated()) {
			// Initialize rollback version functionality
			add_action('admin_init', [$this, 'rollback_init']);
		}
	}

	public function rollback_init()
	{
		if (class_exists('\PixelGalleryPro\Rollback_Version')) {
			$this->rollback_version = new \PixelGalleryPro\Rollback_Version();
		}
	}




	/**
	 * Initialize Custom Code Functionality
	 * 
	 * @access public
	 * @return void
	 */
	public function init_custom_code_functionality()
	{
		// AJAX handler for saving custom code (admin only)
		add_action('wp_ajax_pg_save_custom_code', [$this, 'save_custom_code_ajax']);


		// Admin scripts (admin only)
		add_action('admin_enqueue_scripts', [$this, 'enqueue_custom_code_scripts']);

		// Frontend injection is now handled by global functions in the main plugin file
		self::init_frontend_injection();
	}

	/**
	 * Initialize frontend injection hooks (works on both admin and frontend)
	 * 
	 * @access public static
	 * @return void
	 */
	public static function init_frontend_injection()
	{
		// Frontend hooks are now registered in the main plugin file
		// This method is kept for backwards compatibility but does nothing
	}

	/**
	 * Enqueue scripts for custom code editor
	 * 
	 * @access public
	 * @return void
	 */
	public function enqueue_custom_code_scripts($hook)
	{
		if ($hook !== 'toplevel_page_pixel_gallery_options') {
			return;
		}

		// Enqueue WordPress built-in CodeMirror 
		wp_enqueue_code_editor(array('type' => 'text/css'));
		wp_enqueue_code_editor(array('type' => 'application/javascript'));

		// Enqueue WordPress media library scripts
		wp_enqueue_media();

		// Enqueue the admin script if it exists
		$admin_script_path = BDTPG_ASSETS_PATH . 'js/pg-admin.js';
		if (file_exists($admin_script_path)) {
			wp_enqueue_script(
				'pg-admin-script',
				BDTPG_ASSETS_URL . 'js/pg-admin.js',
				['jquery', 'media-upload', 'media-views', 'code-editor'],
				BDTPG_VER,
				true
			);

			// Localize script with AJAX data
			wp_localize_script('pg-admin-script', 'pg_admin_ajax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('pg_custom_code_nonce'),
				'white_label_nonce' => wp_create_nonce('pg_white_label_nonce')
			]);
		} else {
			// Fallback: localize to jquery if the admin script doesn't exist
			wp_localize_script('jquery', 'pg_admin_ajax', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('pg_custom_code_nonce'),
				'white_label_nonce' => wp_create_nonce('pg_white_label_nonce')
			]);
		}
	}

	/**
	 * AJAX handler for saving white label settings
	 * 
	 * @access public
	 * @return void
	 */
	public function save_white_label_ajax()
	{

		// Check nonce and permissions
		if (!wp_verify_nonce($_POST['nonce'], 'pg_white_label_nonce')) {
			wp_send_json_error(['message' => __('Security check failed', 'pixel-gallery')]);
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('You do not have permission to manage white label settings', 'pixel-gallery')]);
		}

		// Check license eligibility
		if (!self::is_white_label_license()) {
			wp_send_json_error(['message' => __('Your license does not support white label features', 'pixel-gallery')]);
		}

		// Get white label settings
		$white_label_enabled = isset($_POST['pg_white_label_enabled']) ? (bool) $_POST['pg_white_label_enabled'] : false;
		$hide_license = isset($_POST['pg_white_label_hide_license']) ? (bool) $_POST['pg_white_label_hide_license'] : false;
		$bdtpg_hide = isset($_POST['pg_white_label_bdtpg_hide']) ? (bool) $_POST['pg_white_label_bdtpg_hide'] : false;
		$white_label_title = isset($_POST['pg_white_label_title']) ? sanitize_text_field($_POST['pg_white_label_title']) : '';
		$white_label_icon = isset($_POST['pg_white_label_icon']) ? esc_url_raw($_POST['pg_white_label_icon']) : '';
		$white_label_icon_id = isset($_POST['pg_white_label_icon_id']) ? absint($_POST['pg_white_label_icon_id']) : 0;
		$white_label_logo = isset($_POST['pg_white_label_logo']) ? esc_url_raw($_POST['pg_white_label_logo']) : '';
		$pg_white_label_logo_id = isset($_POST['pg_white_label_logo_id']) ? absint($_POST['pg_white_label_logo_id']) : 0;

		// Save settings
		update_option('pg_white_label_enabled', $white_label_enabled);
		update_option('pg_white_label_hide_license', $hide_license);
		update_option('pg_white_label_bdtpg_hide', $bdtpg_hide);
		update_option('pg_white_label_title', $white_label_title);
		update_option('pg_white_label_icon', $white_label_icon);
		update_option('pg_white_label_icon_id', $white_label_icon_id);
		update_option('pg_white_label_logo', $white_label_logo);
		update_option('pg_white_label_logo_id', $pg_white_label_logo_id);

		// Set license title status
		if ($white_label_enabled) {
			update_option('pixel_gallery_license_title_status', true);
		} else {
			delete_option('pixel_gallery_license_title_status');
		}

		// Only send access email if both white label mode AND BDTPG_HIDE are enabled
		if ($white_label_enabled && $bdtpg_hide) {
			$email_sent = $this->send_white_label_access_email();
		}

		wp_send_json_success([
			'message' => __('White label settings saved successfully', 'pixel-gallery'),
			'bdtpg_hide' => $bdtpg_hide,
			'email_sent' => isset($email_sent) ? $email_sent : false
		]);
	}

	/**
	 * Send white label access email with special link
	 * 
	 * @access private
	 * @return bool
	 */
	private function send_white_label_access_email()
	{

		$license_email = self::get_license_email();
		$admin_email = get_bloginfo('admin_email');
		$license_key = self::get_license_key();
		$site_name = get_bloginfo('name');
		$site_url = get_bloginfo('url');

		// Generate secure access token with additional entropy
		$access_token = wp_hash($license_key . time() . wp_salt() . wp_generate_password(32, false));

		// Store access token in database with no expiration
		$token_data = [
			'token' => $access_token,
			'license_key' => $license_key,
			'created_at' => current_time('timestamp'),
			'user_id' => get_current_user_id()
		];

		update_option('pg_white_label_access_token', $token_data);

		// Generate access URL using token instead of license key for security
		// Add white_label_tab=1 parameter to automatically switch to White Label tab
		$access_url = admin_url('admin.php?page=pixel_gallery_options&pg_wl=1&token=' . $access_token . '&white_label_tab=1#pixel_gallery_extra_options');

		// Email subject
		$subject = sprintf('[%s] Pixel Gallery White Label Access Instructions', $site_name);

		// Email message
		$message = $this->get_white_label_email_template($site_name, $site_url, $access_url, $license_key);

		// Email headers
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . $admin_email . '>'
		];

		$email_sent = false;

		// Send to license email
		if (!empty($license_email) && is_email($license_email)) {
			$email_sent = wp_mail($license_email, $subject, $message, $headers);

			// If on localhost or email failed, save email content for manual access
			if (!$email_sent || $this->is_localhost()) {
				$this->save_email_content_for_localhost($access_url, $message, $license_email);
			}
		}

		return $email_sent;
	}

	/**
	 * Check if running on localhost
	 * 
	 * @access private
	 * @return bool
	 */
	private function is_localhost()
	{
		$server_name = $_SERVER['SERVER_NAME'] ?? '';
		$server_addr = $_SERVER['SERVER_ADDR'] ?? '';

		$localhost_indicators = [
			'localhost',
			'127.0.0.1',
			'::1',
			'.local',
			'.test',
			'.dev'
		];

		foreach ($localhost_indicators as $indicator) {
			if (
				strpos($server_name, $indicator) !== false ||
				strpos($server_addr, $indicator) !== false
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Save email content for localhost testing
	 * 
	 * @access private
	 * @param string $access_url
	 * @param string $email_content
	 * @param string $recipient_email
	 * @return void
	 */
	private function save_email_content_for_localhost($access_url, $email_content, $recipient_email)
	{
		$email_data = [
			'access_url' => $access_url,
			'email_content' => $email_content,
			'recipient_email' => $recipient_email,
			'message' => 'Email functionality not available on localhost. Use the access URL below:'
		];

		// Save for admin notice display
		update_option('pg_localhost_email_data', $email_data);
	}

	/**
	 * Get white label email template
	 * 
	 * @access private
	 * @param string $site_name
	 * @param string $site_url  
	 * @param string $access_url
	 * @param string $license_key
	 * @return string
	 */
	private function get_white_label_email_template($site_name, $site_url, $access_url, $license_key)
	{
		$masked_license = substr($license_key, 0, 8) . '****-****-****-' . substr($license_key, -4);

		ob_start();
		?>
		<!DOCTYPE html>
		<html>

		<head>
			<meta charset="UTF-8">
			<title>Pixel Gallery White Label Access</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					line-height: 1.6;
					color: #333;
				}

				.container {
					max-width: 600px;
					margin: 0 auto;
					padding: 20px;
				}

				.header {
					background: #2196F3;
					color: white;
					padding: 20px;
					text-align: center;
					border-radius: 8px 8px 0 0;
				}

				.content {
					background: #f9f9f9;
					padding: 30px;
					border-radius: 0 0 8px 8px;
				}

				.access-link {
					background: #2196F3;
					color: white;
					padding: 15px 25px;
					text-decoration: none;
					border-radius: 5px;
					display: inline-block;
					margin: 20px 0;
				}

				.warning {
					background: #fff3cd;
					border: 1px solid #ffeaa7;
					padding: 15px;
					border-radius: 5px;
					margin: 20px 0;
				}

				.footer {
					margin-top: 30px;
					padding-top: 20px;
					border-top: 1px solid #ddd;
					font-size: 12px;
					color: #666;
				}
			</style>
		</head>

		<body>
			<div class="container">
				<div class="header">
					<h1>🔒 Pixel Gallery White Label Access</h1>
				</div>
				<div class="content">
					<h2>Important: Save This Email!</h2>

					<p>Hello,</p>

					<p>You have successfully enabled <strong>BDTPG_HIDE mode</strong> for Pixel Gallery Pro on
						<strong><?php echo esc_html($site_name); ?></strong>.</p>

					<div class="warning">
						<h3>⚠️ IMPORTANT</h3>
						<p>The plugin interface is hidden from your WordPress admin. Use below link to modify white label
							settings.</p>

						<p style="text-align: center;">
							<a href="<?php echo esc_url($access_url); ?>" class="access-link">Access White Label Settings</a>
						</p>
					</div>

					<p><strong>Direct Link:</strong><br>
						<a href="<?php echo esc_url($access_url); ?>"><?php echo esc_html($access_url); ?></a>
					</p>


					<h3>🔧 What You Can Do</h3>
					<p>Using the access link above, you can:</p>
					<ul>
						<li>Disable BDTPG_HIDE mode</li>
						<li>Modify white label settings</li>
					</ul>

					<p>Need help? <a href="https://bdthemes.com/support/" target="_blank">Contact support</a> with your license
						key.</p>

				</div>
			</div>
		</body>

		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle white label access link
	 * 
	 * @access private
	 * @return void
	 */
	private function handle_white_label_access()
	{
		// Check if this is a white label access request
		if (!isset($_GET['pg_wl']) || !isset($_GET['token'])) {
			return;
		}

		// Check user capability
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

		$pg_wl = sanitize_text_field($_GET['pg_wl']);
		$access_token = sanitize_text_field($_GET['token']);

		// Check if pg_wl is set to 1
		if ($pg_wl !== '1') {
			$this->show_access_error('Invalid access parameter. Please use the correct link from your email.');
			return;
		}

		// Validate the access token
		if (!$this->validate_white_label_access_token($access_token)) {
			$this->show_access_error('Invalid or expired access token. Please use the correct access link from your email.');
			return;
		}

		// Valid access - temporarily allow access by setting a flag
		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', [$this, 'admin_menu'], 201);

		// Add success notice
		add_action('admin_notices', function () {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p><strong>✅ White Label Access Granted!</strong> You can now modify white label settings.</p>';
			echo '</div>';
		});
	}

	/**
	 * Show access error page
	 * 
	 * @access private
	 * @param string $message
	 * @return void
	 */
	private function show_access_error($message)
	{
		wp_die(
			'<h1>🔒 Pixel Gallery White Label Access</h1>' .
			'<p><strong>Access Denied:</strong> ' . esc_html($message) . '</p>' .
			'<p>If you need assistance, please contact support with your license information.</p>' .
			'<p><a href="' . admin_url() . '" class="button button-primary">← Return to Dashboard</a></p>',
			'Access Denied',
			['response' => 403]
		);
	}

	/**
	 * Inject white label icon CSS
	 * 
	 * @access public
	 * @return void
	 */
	public function inject_white_label_icon_css()
	{
		$white_label_enabled = get_option('pg_white_label_enabled', false);
		$white_label_icon = get_option('pg_white_label_icon', '');

		// Only inject CSS when white label is enabled AND a custom icon is set
		if ($white_label_enabled && !empty($white_label_icon)) {
			echo '<style type="text/css">';
			echo '#toplevel_page_pixel_gallery_options .wp-menu-image {';
			echo 'background-image: url(' . esc_url($white_label_icon) . ') !important;';
			echo 'background-size: 20px 20px !important;';
			echo 'background-repeat: no-repeat !important;';
			echo 'background-position: center !important;';
			echo '}';
			echo '#toplevel_page_pixel_gallery_options .wp-menu-image:before {';
			echo 'display: none !important;';
			echo '}';
			echo '#toplevel_page_pixel_gallery_options .wp-menu-image img {';
			echo 'display: none !important;';
			echo '}';
			echo '</style>';
		}
		// When white label is disabled or no icon is set, don't inject any CSS
		// This allows WordPress's original icon to display naturally
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

			$old_error_level = error_reporting();
			error_reporting(E_ALL & ~E_WARNING); // Suppress warnings
			$elements = $module->get_formatted_usage('raw');
			error_reporting($old_error_level); // Restore

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

			$old_error_level = error_reporting();
			error_reporting(E_ALL & ~E_WARNING); // Suppress warnings
			$elements = $module->get_formatted_usage('raw');
			error_reporting($old_error_level); // Restore

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
		if (isset($_GET['page']) && $_GET['page'] === self::PAGE_ID . '_get_pro') {
			wp_redirect('https://pixelgallery.pro/pricing/');
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
		if (isset($_GET['page']) && $_GET['page'] === self::PAGE_ID . '_license_renew') {
			wp_redirect('https://account.bdthemes.com/');
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

		if (!defined('BDTPG_LO')) {
			add_submenu_page(
				self::PAGE_ID,
				BDTPG_TITLE,
				esc_html__('Special Features', 'pixel-gallery'),
				'manage_options',
				self::PAGE_ID . '#pixel_gallery_other_settings',
				[$this, 'plugin_page']
			);
		}

		add_submenu_page(
			self::PAGE_ID,
			BDTPG_TITLE,
			esc_html__('Extra Options', 'pixel-gallery'),
			'manage_options',
			self::PAGE_ID . '#pixel_gallery_extra_options',
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

		if (true == _is_pg_pro_activated()) {
			add_submenu_page(
				self::PAGE_ID,
				BDTPG_TITLE,
				esc_html__('Rollback Version', 'pixel-gallery'),
				'manage_options',
				self::PAGE_ID . '#pixel_gallery_rollback_version',
				[$this, 'plugin_page']
			);
		}
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
			[
				'id' => 'pixel_gallery_other_settings',
				'title' => esc_html__('Special Features', 'pixel-gallery'),
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
							<h1 class="pg-feature-title"><?php echo esc_html__('All Widgets', 'pixel-gallery'); ?></h1>
							<div class="bdt-flex bdt-flex-between bdt-flex-middle">
								<div class="pg-count-wrap">
									<div class="pg-widget-count"><?php echo esc_html__('Used:', 'pixel-gallery'); ?>
										<b><?php echo esc_html__($used_widgets, 'pixel-gallery'); ?></b></div>
									<div class="pg-widget-count"><?php echo esc_html__('Unused:', 'pixel-gallery'); ?>
										<b><?php echo esc_html__($un_used_widgets, 'pixel-gallery'); ?></b></div>
									<div class="pg-widget-count"><?php echo esc_html__('Total:', 'pixel-gallery'); ?>
										<b><?php echo esc_html__($used_widgets + $un_used_widgets, 'pixel-gallery'); ?></b>
									</div>
								</div>

								<div class="pg-canvas-wrap">
									<canvas id="bdt-db-total-status" style="height: 100px; width: 100px;"
										data-label="Total Widgets Status - (<?php echo esc_html__($used_widgets + $un_used_widgets, 'pixel-gallery'); ?>)"
										data-labels="<?php echo esc_attr('Used, Unused'); ?>"
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
							<h1 class="pg-feature-title"><?php echo esc_html_e('Active', 'pixel-gallery'); ?></h1>
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
						<h1 class="pg-feature-title">Support And Feedback</h1>
						<p>Feeling like to consult with an expert? Take live Chat support immediately from <a
								href="https://pixelgallery.com" target="_blank" rel="">PixelGallery</a>. We are always
							ready to help
							you 24/7.</p>
						<p><strong>Or if you’re facing technical issues with our plugin, then please create a support
								ticket</strong></p>
						<a class="bdt-button bdt-btn-blue bdt-margin-small-top bdt-margin-small-right" target="_blank" rel=""
							href="https://bdthemes.com/all-knowledge-base-of-pixel-gallery/">Knowledge
							Base</a>
						<a class="bdt-button bdt-btn-grey bdt-margin-small-top" target="_blank"
							href="https://bdthemes.com/support/">Get Support</a>
					</div>
				</div>

				<div class="bdt-width-3-5@m">
					<div class="bdt-card bdt-card-body pg-system-requirement">
						<h1 class="pg-feature-title bdt-margin-small-bottom">System Requirement</h1>
						<?php $this->pixel_gallery_system_requirement(); ?>
					</div>
				</div>
			</div>

			<div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
				<div class="bdt-width-1-2@m pg-support-section">
					<div class="bdt-card bdt-card-body pg-feedback-bg">
						<h1 class="pg-feature-title">Missing Any Feature?</h1>
						<p style="max-width: 520px;">Are you in need of a feature that’s not available in our plugin?
							Feel free to do a feature request from here,</p>
						<a class="bdt-button bdt-btn-yellow bdt-margin-small-top" target="_blank" rel=""
							href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/">Request Feature</a>
					</div>
				</div>

				<div class="bdt-width-1-2@m">
					<div class="bdt-card bdt-card-body pg-tryaddon-bg">
						<h1 class="pg-feature-title">Try Our Others Addons</h1>
						<p style="max-width: 520px;">
							<b>Element Pack, Prime Slider, Pixel Gallery & Ultimate Store Kit</b> addons for <b>Elementor</b> is
							the best slider &
							blogs plugin for WordPress.
						</p>
						<div class="bdt-others-plugins-link">
							<a class="bdt-button bdt-btn-ep bdt-margin-small-right" target="_blank"
								href="https://wordpress.org/plugins/bdthemes-element-pack-lite/"
								bdt-tooltip="Element Pack Lite provides more than 50+ essential elements for everyday applications to simplify the whole web building process. It's Free! Download it.">Element
								pack</a>
							<a class="bdt-button bdt-btn-ps bdt-margin-small-right" target="_blank"
								href="https://wordpress.org/plugins/bdthemes-prime-slider-lite/"
								bdt-tooltip="The revolutionary slider builder addon for Elementor with next-gen superb interface. It's Free! Download it.">Prime
								Slider</a>
							<a class="bdt-button bdt-btn-pg bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/pixel-gallery/"
								bdt-tooltip="Best blogging addon for building quality blogging website with fine-tuned features and widgets. It's Free! Download it.">Pixel
								Gallery</a>
							<a class="bdt-button bdt-btn-usk bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/ultimate-store-kit/"
								bdt-tooltip="The only eCommmerce addon for answering all your online store design problems in one package. It's Free! Download it.">Ultimate
								Store Kit</a>
							<a class="bdt-button bdt-btn-live-copy bdt-margin-small-right" target="_blank" rel=""
								href="https://wordpress.org/plugins/live-copy-paste/"
								bdt-tooltip="Superfast cross-domain copy-paste mechanism for WordPress websites with true UI copy experience. It's Free! Download it.">Live
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
					<a href="<?php echo admin_url('?pg_setup_wizard=show'); ?>"
						class="bdt-button bdt-welcome-button bdt-margin-small-top"
						target="_blank"><?php esc_html_e('Setup Pixel Gallery', 'pixel-gallery'); ?></a>

					<div class="pg-dashboard-compare-section">
						<h4 class="pg-feature-sub-title">
							<?php printf(esc_html__('Unlock %sPremium Features%s', 'pixel-gallery'), '<strong class="pg-highlight-text">', '</strong>'); ?>
						</h4>
						<h1 class="pg-feature-title pg-dashboard-compare-title">
							<?php esc_html_e('Create Your Sleek Website with Pixel Gallery Pro!', 'pixel-gallery'); ?>
						</h1>
						<p><?php esc_html_e('Don\'t need more plugins. This pro addon helps you build complex or professional websites—visually stunning, functional and customizable.', 'pixel-gallery'); ?>
						</p>
						<ul>
							<li><?php esc_html_e('Asset Manager', 'pixel-gallery'); ?></li>
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
						<img src="<?php echo BDTPG_ADMIN_URL . 'assets/images/template.jpg'; ?>"
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
						<img src="<?php echo BDTPG_ADMIN_URL . 'assets/images/support.svg'; ?>"
							alt="Pixel Gallery Dashboard Template">
						<h1 class="pg-feature-title">
							<?php esc_html_e('Getting Started with Quick Access', 'pixel-gallery'); ?>
						</h1>
						<ul>
							<li><a href="https://pixelgallery.pro/contact/"
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
				<a href="https://bdthemes.com/all-knowledge-base-of-pixel-gallery/" target="_blank"
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
							<h1 class="bdt-text-bold">WHY GO WITH PRO?</h1>
							<h2>Just Compare With Pixel Gallery Free Vs Pro</h2>
						</div>
						<?php if (true !== _is_pg_pro_activated()): ?>
							<div class="pg-purchase-button">
								<a href="https://pixelgallery.pro/pricing/" target="_blank">Purchase Now</a>
							</div>
						<?php endif; ?>
					</div>

					<div>

						<ul class="bdt-list bdt-list-divider bdt-text-left bdt-text-normal" style="font-size: 15px;">


							<li class="bdt-text-bold">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m">Features</div>
									<div class="bdt-width-auto@m">Free</div>
									<div class="bdt-width-auto@m">Pro</div>
								</div>
							</li>
							<li class="">
								<div class="bdt-grid">
									<div class="bdt-width-expand@m"><span
											bdt-tooltip="pos: top-left; title: Lite have 35+ Widgets but Pro have 100+ core widgets">Core
											Widgets</span></div>
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
									<a href="https://pixelgallery.pro/pricing/" target="_blank">Purchase Now</a>
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
					bdt-sticky="offset: 32; animation: bdt-animation-slide-top-small; duration: 300">

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
							$white_label_enabled = get_option('pg_white_label_enabled', false);
							$white_label_logo = get_option('pg_white_label_logo', '');
							$white_label_title = get_option('pg_white_label_title', '');

							if ($white_label_enabled && !empty($white_label_logo)) {

								$alt_text = !empty($white_label_title) ? $white_label_title . ' Logo' : 'Custom Logo';
								echo '<img src="' . esc_url($white_label_logo) . '" alt="' . esc_attr($alt_text) . '" style="max-height: 40px;">';
							} else {
								echo '<img src="' . BDTPG_URL . 'assets/images/logo-with-text.svg" alt="Pixel Gallery Logo">';
							}
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

						<!-- Custom Code Save Button Section -->
						<div class="pg-code-save-section" style="display: none;">
							<button type="button" id="pg-save-custom-code"
								class="bdt-button bdt-button-primary pixel-gallery-custom-code-save-btn">
								<?php esc_html_e('Save Custom Code', 'pixel-gallery'); ?>
							</button>
							<button type="button" id="pg-reset-custom-code"
								class="bdt-button bdt-button-primary pixel-gallery-custom-code-reset-btn">
								<?php esc_html_e('Reset Code', 'pixel-gallery'); ?>
							</button>
						</div>

						<!--  White Label Save Button Section -->
						<?php if (self::is_white_label_license()): ?>
							<div class="pg-white-label-save-section" style="display: none;">
								<button type="button" id="pg-save-white-label"
									class="bdt-button bdt-button-primary pixel-gallery-white-label-save-btn">
									<?php esc_html_e('Save White Label Settings', 'pixel-gallery'); ?>
								</button>
							</div>
						<?php endif; ?>

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
							bdt-sticky="end: !.pg-dashboard-container; offset: 115; animation: bdt-animation-slide-top-small; duration: 300">

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
						<div id="pixel_gallery_welcome_page" class="pg-option-page group">
							<?php $this->pixel_gallery_welcome(); ?>
						</div>

						<?php $this->settings_api->show_forms(); ?>

						<div id="pixel_gallery_extra_options_page" class="pg-option-page group">
							<?php $this->pixel_gallery_extra_options(); ?>
						</div>

						<div id="pixel_gallery_analytics_system_req_page" class="pg-option-page group">
							<?php $this->pixel_gallery_analytics_system_req_content(); ?>
						</div>

						<div id="pixel_gallery_other_plugins_page" class="pg-option-page group">
							<?php $this->pixel_gallery_others_plugin(); ?>
						</div>

						<!-- <div id="pixel_gallery_affiliate_page" class="pg-option-page group">
							<?php //$this->pixel_gallery_affiliate_content(); ?>
						</div> -->

						<?php if (true == _is_pg_pro_activated()): ?>
							<div id="pixel_gallery_rollback_version_page" class="pg-option-page group">
								<?php $this->pg_rollback_version_content(); ?>
							</div>
						<?php endif; ?>

						<?php if (_is_pg_pro_activated() !== true): ?>
							<div id="pixel_gallery_get_pro" class="pg-option-page group">
								<?php $this->pixel_gallery_get_pro(); ?>
							</div>
						<?php endif; ?>

						<div id="pixel_gallery_license_settings_page" class="pg-option-page group">

							<?php
							if (_is_pg_pro_activated() == true) {
								apply_filters('pg_license_page', '');
							}

							?>
						</div>

					</div>
				</div>

				<?php if (!defined('BDTPG_WL') || false == self::license_wl_status()) {
					$this->footer_info();
				} ?>
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
				var parentID = '#' + jQuery(e).data('id');
				var search = jQuery(parentID).find('.bdt-search-input').val().toLowerCase();

				jQuery(".pg-options .pg-option-item").filter(function () {
					jQuery(this).toggle(jQuery(this).attr('data-widget-name').toLowerCase().indexOf(search) > -1)
				});

				if (!search) {
					jQuery(parentID).find('.bdt-search-input').attr('bdt-filter-control', "");
					jQuery(parentID).find('.pg-widget-all').trigger('click');
				} else {
					jQuery(parentID).find('.bdt-search-input').attr('bdt-filter-control', "filter: [data-widget-name*='" + search + "']");
					jQuery(parentID).find('.bdt-search-input').removeClass('bdt-active'); // Thanks to Bar-Rabbas
					jQuery(parentID).find('.bdt-search-input').trigger('click');
				}
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

					jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function () {
						jQuery(this).attr('checked', 'checked').prop("checked", true);
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_active_modules_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();
					jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function () {
						jQuery(this).removeAttr('checked');
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-active-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					jQuery('#pixel_gallery_elementor_extend_page .checkbox:visible').each(function () {
						jQuery(this).attr('checked', 'checked').prop("checked", true);
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
				});

				jQuery('#pixel_gallery_elementor_extend_page a.pg-deactive-all-widget').on('click', function (e) {
					e.preventDefault();
					jQuery('#pixel_gallery_elementor_extend_page .checkbox:visible').each(function () {
						jQuery(this).removeAttr('checked');
					});

					jQuery(this).addClass('bdt-active');
					jQuery('a.pg-active-all-widget').removeClass('bdt-active');
				});

				// Activate/Deactivate all widgets functionality
				$('#pixel_gallery_active_modules_page a.pg-active-all-widget').on('click', function (e) {
					e.preventDefault();

					$('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function () {
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

					$('#pixel_gallery_active_modules_page .checkbox:visible').each(function () {
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

					$('#pixel_gallery_elementor_extend_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function () {
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

					$('#pixel_gallery_elementor_extend_page .checkbox:visible').each(function () {
						$(this).removeAttr('checked').prop("checked", false);
					});

					$(this).addClass('bdt-active');
					$('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').removeClass('bdt-active');

					// Ensure save button remains visible
					setTimeout(function () {
						$('.pg-dashboard-save-btn').show();
					}, 100);
				});

				jQuery('#pixel_gallery_active_modules_page .pg-pro-inactive .checkbox').each(function () {
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
					'pixel_gallery_other_settings',        // Special features
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
				$(document).on('change', '#pixel_gallery_elementor_extend_page .checkbox, #pixel_gallery_active_modules_page .checkbox', function () {
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
						targetForm = $('#' + currentHash + '_page form.settings-save');

						// If not found, try without _page suffix
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + ' form.settings-save');
						}

						// Try to find any form in the active tab content
						if (!targetForm || targetForm.length === 0) {
							targetForm = $('#' + currentHash + '_page form');
						}
					}

					// Fallback to any visible form with settings-save class
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('form.settings-save:visible').first();
					}

					// Last fallback - any visible form
					if (!targetForm || targetForm.length === 0) {
						targetForm = $('.bdt-switcher .group:visible form').first();
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

				//White Label Settings Functionality
				//Check if pg_admin_ajax is available
				if (typeof pg_admin_ajax === 'undefined') {
					window.pg_admin_ajax = {
						ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
						white_label_nonce: '<?php echo wp_create_nonce('pg_white_label_nonce'); ?>'
					};
				}

				// Initialize CodeMirror editors for custom code
				var codeMirrorEditors = {};

				function initializeCodeMirrorEditors() {
					// CSS Editor 1
					if (document.getElementById('pg-custom-css')) {
						codeMirrorEditors['pg-custom-css'] = wp.codeEditor.initialize('pg-custom-css', {
							type: 'text/css',
							codemirror: {
								lineNumbers: true,
								mode: 'css',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}

					// JavaScript Editor 1
					if (document.getElementById('pg-custom-js')) {
						codeMirrorEditors['pg-custom-js'] = wp.codeEditor.initialize('pg-custom-js', {
							type: 'application/javascript',
							codemirror: {
								lineNumbers: true,
								mode: 'javascript',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}

					// CSS Editor 2
					if (document.getElementById('pg-custom-css-2')) {
						codeMirrorEditors['pg-custom-css-2'] = wp.codeEditor.initialize('pg-custom-css-2', {
							type: 'text/css',
							codemirror: {
								lineNumbers: true,
								mode: 'css',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}

					// JavaScript Editor 2
					if (document.getElementById('pg-custom-js-2')) {
						codeMirrorEditors['pg-custom-js-2'] = wp.codeEditor.initialize('pg-custom-js-2', {
							type: 'application/javascript',
							codemirror: {
								lineNumbers: true,
								mode: 'javascript',
								theme: 'default',
								lineWrapping: true,
								autoCloseBrackets: true,
								matchBrackets: true,
								lint: false
							}
						});
					}

					// Refresh all editors after a short delay to ensure proper rendering
					setTimeout(function () {
						refreshAllCodeMirrorEditors();
					}, 100);
				}

				// Function to refresh all CodeMirror editors
				function refreshAllCodeMirrorEditors() {
					Object.keys(codeMirrorEditors).forEach(function (editorKey) {
						if (codeMirrorEditors[editorKey] && codeMirrorEditors[editorKey].codemirror) {
							codeMirrorEditors[editorKey].codemirror.refresh();
						}
					});
				}

				// Function to refresh editors when tab becomes visible
				function refreshEditorsOnTabShow() {
					// Listen for tab changes (UIkit tab switching)
					if (typeof bdtUIkit !== 'undefined' && bdtUIkit.tab) {
						// When tab becomes active, refresh editors
						bdtUIkit.util.on(document, 'shown', '.bdt-tab', function () {
							setTimeout(function () {
								refreshAllCodeMirrorEditors();
							}, 50);
						});
					}

					// Also listen for direct tab clicks
					$('.bdt-tab a').on('click', function () {
						setTimeout(function () {
							refreshAllCodeMirrorEditors();
						}, 100);
					});

					// Listen for switcher changes (UIkit switcher)
					if (typeof bdtUIkit !== 'undefined' && bdtUIkit.switcher) {
						bdtUIkit.util.on(document, 'shown', '.bdt-switcher', function () {
							setTimeout(function () {
								refreshAllCodeMirrorEditors();
							}, 50);
						});
					}
				}

				// Initialize editors when page loads - with delay for better rendering
				setTimeout(function () {
					initializeCodeMirrorEditors();
				}, 100);

				// Setup tab switching handlers
				setTimeout(function () {
					refreshEditorsOnTabShow();
				}, 100);

				// Handle window resize events
				$(window).on('resize', function () {
					setTimeout(function () {
						refreshAllCodeMirrorEditors();
					}, 100);
				});

				// Handle page visibility changes (when switching browser tabs)
				document.addEventListener('visibilitychange', function () {
					if (!document.hidden) {
						setTimeout(function () {
							refreshAllCodeMirrorEditors();
						}, 200);
					}
				});

				// Force refresh when clicking on the Custom CSS & JS tab specifically
				$('a[href="#"]').on('click', function () {
					var tabText = $(this).text().trim();
					if (tabText === 'Custom CSS & JS') {
						setTimeout(function () {
							refreshAllCodeMirrorEditors();
						}, 150);
					}
				});

				//Toggle white label fields visibility
				$('#pg-white-label-enabled').on('change', function () {
					if ($(this).is(':checked')) {
						$('.pg-white-label-fields').slideDown(300);
					} else {
						$('.pg-white-label-fields').slideUp(300);
					}
				});

				//WordPress Media Library Integration for Icon Upload
				var mediaUploader;

				$('#pg-upload-icon').on('click', function (e) {
					e.preventDefault();

					// If the uploader object has already been created, reopen the dialog
					if (mediaUploader) {
						mediaUploader.open();
						return;
					}

					// Create the media frame
					mediaUploader = wp.media.frames.file_frame = wp.media({
						title: 'Select Icon',
						button: {
							text: 'Use This Icon'
						},
						library: {
							type: ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml']
						},
						multiple: false
					});

					// When an image is selected, run a callback
					mediaUploader.on('select', function () {
						var attachment = mediaUploader.state().get('selection').first().toJSON();

						// Set the hidden inputs
						$('#pg-white-label-icon').val(attachment.url);
						$('#pg-white-label-icon-id').val(attachment.id);

						// Update preview
						$('#pg-icon-preview-img').attr('src', attachment.url);
						$('.pg-icon-preview-container').show();
					});

					// Open the uploader dialog
					mediaUploader.open();
				});

				//Remove icon functionality
				$('#pg-remove-icon').on('click', function (e) {
					e.preventDefault();

					// Clear the hidden inputs
					$('#pg-white-label-icon').val('');
					$('#pg-white-label-icon-id').val('');

					// Hide preview
					$('.pg-icon-preview-container').hide();
					$('#pg-icon-preview-img').attr('src', '');
				});

				// WordPress Media Library Integration for Logo Upload
				var logoUploader;

				$('#pg-upload-logo').on('click', function (e) {
					e.preventDefault();

					// If the uploader object has already been created, reopen the dialog
					if (logoUploader) {
						logoUploader.open();
						return;
					}

					// Create the media frame
					logoUploader = wp.media.frames.file_frame = wp.media({
						title: 'Select Logo',
						button: {
							text: 'Use This Logo'
						},
						library: {
							type: ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml']
						},
						multiple: false
					});

					// When an image is selected, run a callback
					logoUploader.on('select', function () {
						var attachment = logoUploader.state().get('selection').first().toJSON();

						// Set the hidden inputs
						$('#pg-white-label-logo').val(attachment.url);
						$('#pg-white-label-logo-id').val(attachment.id);

						// Update preview
						$('#pg-logo-preview-img').attr('src', attachment.url);
						$('.pg-logo-preview-container').show();
					});

					// Open the uploader dialog
					logoUploader.open();
				});

				// Remove logo functionality
				$('#pg-remove-logo').on('click', function (e) {
					e.preventDefault();

					// Clear the hidden inputs
					$('#pg-white-label-logo').val('');
					$('#pg-white-label-logo-id').val('');

					// Hide preview
					$('.pg-logo-preview-container').hide();
					$('#pg-logo-preview-img').attr('src', '');
				});

				//BDTPG_HIDE Warning when checkbox is enabled
				$('#pg-white-label-bdtpg-hide').on('change', function () {
					if ($(this).is(':checked')) {
						// Show warning modal/alert
						var warningMessage = '⚠️ WARNING: ADVANCED FEATURE\n\n' +
							'Enabling BDTPG_HIDE will activate advanced white label mode that:\n\n' +
							'• Hides ALL Element Pack branding and menus\n' +
							'• Makes these settings difficult to access later\n' +
							'• Requires the special access link to return\n' +
							'• Is intended for client/agency use only\n\n' +
							'An email with access instructions will be sent if you proceed.\n\n' +
							'Are you sure you want to enable this advanced mode?';

						if (!confirm(warningMessage)) {
							// User cancelled, uncheck the box
							$(this).prop('checked', false);
							return false;
						}

						// Show additional info message
						if ($('#pg-bdtpg-hide-info').length === 0) {
							$(this).closest('.pg-option-item').after(
								'<div id="pg-bdtpg-hide-info" class="bdt-alert bdt-alert-warning bdt-margin-small-top">' +
								'<p><strong>BDTPG_HIDE Mode Enabled</strong></p>' +
								'<p>When you save these settings, an email will be sent with instructions to access white label settings in the future.</p>' +
								'</div>'
							);
						}
					} else {
						// Remove info message when unchecked
						$('#pg-bdtpg-hide-info').remove();
					}
				});

				// Save white label settings with confirmation
				$('#pg-save-white-label').on('click', function (e) {
					e.preventDefault();

					// Check if button is disabled (no license or no white label eligible license)
					if ($(this).prop('disabled')) {
						var buttonText = $(this).text().trim();
						var alertMessage = '';

						if (buttonText.includes('License Not Activated')) {
							alertMessage = '<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p><strong>License Not Activated</strong><br>You need to activate your Pixel Gallery license to access White Label functionality. Please activate your license first.</p>' +
								'</div>';
						} else {
							alertMessage = '<div class="bdt-alert bdt-alert-warning" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p><strong>Eligible License Required</strong><br>White Label functionality is available for Agency, Extended, Developer, AppSumo Lifetime, and other eligible license holders. Please upgrade your license to access these features.</p>' +
								'</div>';
						}

						$('#pg-white-label-message').html(alertMessage).show();
						return false;
					}

					// Check if white label mode is being enabled
					var whiteLabelEnabled = $('#pg-white-label-enabled').is(':checked');
					var bdtpgHideEnabled = $('#pg-white-label-bdtpg-hide').is(':checked');

					// Only show confirmation dialog if white label is enabled AND BDTPG_HIDE is enabled
					if (whiteLabelEnabled && bdtpgHideEnabled) {
						var confirmMessage = '🔒 FINAL CONFIRMATION\n\n' +
							'You are about to save settings with BDTPG_HIDE enabled.\n\n' +
							'This will:\n' +
							'• Hide Pixel Gallery from WordPress admin immediately\n' +
							'• Send access instructions to your email addresses\n' +
							'• Require the special link to modify these settings\n\n' +
							'Email will be sent to:\n' +
							'• License email: <?php echo esc_js(self::get_license_email()); ?>\n' +
							'Are you absolutely sure you want to proceed?';

						if (!confirm(confirmMessage)) {
							return false;
						}
					}

					var $button = $(this);
					var originalText = $button.html();

					// Show loading state
					$button.html('Saving...');
					$button.prop('disabled', true);

					// Collect form data
					var formData = {
						action: 'pg_save_white_label',
						nonce: pg_admin_ajax.white_label_nonce,
						pg_white_label_enabled: $('#pg-white-label-enabled').is(':checked') ? 1 : 0,
						pg_white_label_title: $('#pg-white-label-title').val(),
						pg_white_label_icon: $('#pg-white-label-icon').val(),
						pg_white_label_icon_id: $('#pg-white-label-icon-id').val(),
						pg_white_label_logo: $('#pg-white-label-logo').val(),
						pg_white_label_logo_id: $('#pg-white-label-logo-id').val(),
						pg_white_label_hide_license: $('#pg-white-label-hide-license').is(':checked') ? 1 : 0,
						pg_white_label_bdtpg_hide: $('#pg-white-label-bdtpg-hide').is(':checked') ? 1 : 0
					};

					// Send AJAX request
					$.post(pg_admin_ajax.ajax_url, formData)
						.done(function (response) {
							if (response.success) {
								// Show success message with countdown
								var countdown = 2;
								var successMessage = response.data.message;

								// Add email notification info if BDTPG_HIDE was enabled
								if (response.data.bdtpg_hide && response.data.email_sent) {
									successMessage += '<br><br><strong>📧 Access Email Sent!</strong><br>Check your email for the access link to modify these settings in the future.';
								} else if (response.data.bdtpg_hide && !response.data.email_sent && response.data.access_url) {
									// Localhost scenario - show the access URL directly
									successMessage += '<br><br><strong>📧 Localhost Email Notice:</strong><br>Email functionality is not available on localhost.<br><strong>Your Access URL:</strong><br><a href="' + response.data.access_url + '" target="_blank">Click here to access white label settings</a><br><small>Save this URL - you\'ll need it to modify settings when BDTPG_HIDE is active.</small>';
								} else if (response.data.bdtpg_hide && !response.data.email_sent) {
									successMessage += '<br><br><strong>⚠️ Email Notice:</strong><br>There was an issue sending the access email. Please check your email settings or contact support.';
								}

								$('#pg-white-label-message').html(
									'<div class="bdt-alert bdt-alert-success" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>' + successMessage + ' <span id="pg-reload-countdown">Reloading in ' + countdown + ' seconds...</span></p>' +
									'</div>'
								).show();

								// Update button text
								$button.html('Reloading...');

								// Countdown timer
								var countdownInterval = setInterval(function () {
									countdown--;
									if (countdown > 0) {
										$('#pg-reload-countdown').text('Reloading in ' + countdown + ' seconds...');
									} else {
										$('#pg-reload-countdown').text('Reloading now...');
										clearInterval(countdownInterval);
									}
								}, 1000);

								// Check if BDTPG_HIDE is enabled and redirect accordingly
								setTimeout(function () {
									if (response.data.bdtpg_hide) {
										// Redirect to admin dashboard if BDTPG_HIDE is enabled
										window.location.href = '<?php echo admin_url('index.php'); ?>';
									} else {
										// Reload current page if BDTPG_HIDE is not enabled
										window.location.reload();
									}
								}, 1500);
							} else {
								// Show error message
								$('#pg-white-label-message').html(
									'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>Error: ' + (response.data.message || 'Unknown error occurred') + '</p>' +
									'</div>'
								).show();

								// Restore button state for error case
								$button.html(originalText);
								$button.prop('disabled', false);
							}
						})
						.fail(function (xhr, status, error) {
							// Show error message
							$('#pg-white-label-message').html(
								'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p>Error: Failed to save settings. Please try again. (' + status + ')</p>' +
								'</div>'
							).show();

							// Restore button state for failure case
							$button.html(originalText);
							$button.prop('disabled', false);
						});
				});

				// Save custom code functionality (updated for CodeMirror)
				$('#pg-save-custom-code').on('click', function (e) {
					e.preventDefault();

					var $button = $(this);
					var originalText = $button.html();

					// Check if pg_admin_ajax is available
					if (typeof pg_admin_ajax === 'undefined') {
						$('#pg-custom-code-message').html(
							'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
							'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
							'<p>Error: AJAX configuration not loaded. Please refresh the page and try again.</p>' +
							'</div>'
						).show();
						return;
					}

					// Prevent multiple simultaneous saves
					if ($button.prop('disabled') || $button.hasClass('pg-saving')) {
						return;
					}

					// Mark as saving
					$button.addClass('pg-saving');

					// Get content from CodeMirror editors
					function getCodeMirrorContent(elementId) {
						if (codeMirrorEditors[elementId] && codeMirrorEditors[elementId].codemirror) {
							return codeMirrorEditors[elementId].codemirror.getValue();
						} else {
							// Fallback to textarea value
							return $('#' + elementId).val() || '';
						}
					}

					var cssContent = getCodeMirrorContent('pg-custom-css');
					var jsContent = getCodeMirrorContent('pg-custom-js');
					var css2Content = getCodeMirrorContent('pg-custom-css-2');
					var js2Content = getCodeMirrorContent('pg-custom-js-2');

					// Show loading state
					$button.prop('disabled', true);

					// Timeout safeguard - if AJAX doesn't complete in 30 seconds, restore button
					var timeoutId = setTimeout(function () {
						$button.removeClass('pg-saving');
						$button.html(originalText);
						$button.prop('disabled', false);
						$('#pg-custom-code-message').html(
							'<div class="bdt-alert bdt-alert-warning" bdt-alert>' +
							'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
							'<p>Save operation timed out. Please try again.</p>' +
							'</div>'
						).show();
					}, 30000);

					// Collect form data
					var formData = {
						action: 'pg_save_custom_code',
						nonce: pg_admin_ajax.nonce,
						custom_css: cssContent,
						custom_js: jsContent,
						custom_css_2: css2Content,
						custom_js_2: js2Content,
						excluded_pages: $('#pg-excluded-pages').val() || []
					};


					// Verify we have some content before sending (optional check)
					var totalContentLength = cssContent.length + jsContent.length + css2Content.length + js2Content.length;
					if (totalContentLength === 0) {
						var confirmEmpty = confirm('No content detected in any editor. Do you want to save empty content (this will clear all custom code)?');
						if (!confirmEmpty) {
							// Restore button state
							$button.html(originalText);
							$button.prop('disabled', false);
							return;
						}
					}

					// Send AJAX request
					$.post(pg_admin_ajax.ajax_url, formData)
						.done(function (response) {
							console.log('AJAX Response:', response); // Debug log

							if (response && response.success) {
								// Show success message
								var successMessage = response.data.message;
								if (response.data.excluded_count) {
									successMessage += ' (' + response.data.excluded_count + ' pages excluded)';
								}

								$('#pg-custom-code-message').html(
									'<div class="bdt-alert bdt-alert-success" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>' + successMessage + '</p>' +
									'</div>'
								).show();

								// Auto-hide message after 5 seconds
								setTimeout(function () {
									$('#pg-custom-code-message').fadeOut();
								}, 5000);

							} else {
								// Show error message
								var errorMessage = 'Unknown error occurred';
								if (response && response.data && response.data.message) {
									errorMessage = response.data.message;
								} else if (response && response.message) {
									errorMessage = response.message;
								}

								$('#pg-custom-code-message').html(
									'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p>Error: ' + errorMessage + '</p>' +
									'</div>'
								).show();
							}
						})
						.fail(function (xhr, status, error) {
							console.log('AJAX Error:', xhr, status, error); // Debug log

							// Try to parse error response
							var errorMessage = 'Failed to save custom code. Please try again.';
							try {
								var errorResponse = JSON.parse(xhr.responseText);
								if (errorResponse.data && errorResponse.data.message) {
									errorMessage = errorResponse.data.message;
								} else if (errorResponse.message) {
									errorMessage = errorResponse.message;
								}
							} catch (e) {
								// Use default error message
							}

							// Show error message
							$('#pg-custom-code-message').html(
								'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
								'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
								'<p>Error: ' + errorMessage + ' (' + status + ')</p>' +
								'</div>'
							).show();
						})
						.always(function () {

							// Clear the timeout since AJAX completed
							clearTimeout(timeoutId);

							try {
								$button.removeClass('pg-saving');
								$button.html(originalText);
								$button.prop('disabled', false);
							} catch (e) {
								// Fallback: force button restoration
								$('#pg-save-custom-code').removeClass('pg-saving').html('<span class="dashicons dashicons-yes"></span> Save Custom Code').prop('disabled', false);
							}
						});
				});

				// Reset custom code functionality (updated for CodeMirror)
				$('#pg-reset-custom-code').on('click', function (e) {
					e.preventDefault();

					if (confirm('Are you sure you want to reset all custom code? This will clear all code.')) {
						var $button = $(this);
						var originalText = $button.html();

						// Clear CodeMirror editors
						function clearCodeMirrorEditor(elementId) {
							if (codeMirrorEditors[elementId] && codeMirrorEditors[elementId].codemirror) {
								codeMirrorEditors[elementId].codemirror.setValue('');
							} else {
								// Fallback to clearing textarea
								$('#' + elementId).val('');
							}
						}

						// Clear all editors
						clearCodeMirrorEditor('pg-custom-css');
						clearCodeMirrorEditor('pg-custom-js');
						clearCodeMirrorEditor('pg-custom-css-2');
						clearCodeMirrorEditor('pg-custom-js-2');

						// Clear exclusions
						$('#pg-excluded-pages').val([]).trigger('change');

						// Show clearing message
						$('#pg-custom-code-message').html(
							'<div class="bdt-alert bdt-alert-primary" bdt-alert>' +
							'<p><span bdt-spinner="ratio: 0.6"></span> Clearing custom code...</p>' +
							'</div>'
						).show();

						// Disable button during save
						$button.prop('disabled', true).html('<span bdt-spinner="ratio: 0.6"></span> Resetting...');

						// Prepare empty data for AJAX save
						var formData = {
							action: 'pg_save_custom_code',
							nonce: pg_admin_ajax.nonce,
							custom_css: '',
							custom_js: '',
							custom_css_2: '',
							custom_js_2: '',
							excluded_pages: []
						};

						// Send AJAX request to save empty values
						$.ajax({
							url: pg_admin_ajax.ajax_url,
							type: 'POST',
							data: formData,
							timeout: 30000,
							success: function (response) {
								if (response.success) {
									// Show success message
									$('#pg-custom-code-message').html(
										'<div class="bdt-alert bdt-alert-success" bdt-alert>' +
										'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
										'<p><span class="dashicons dashicons-yes"></span> All custom code has been reset successfully!</p>' +
										'</div>'
									).show();

									// Auto-hide message after 5 seconds
									setTimeout(function () {
										$('#pg-custom-code-message').fadeOut();
									}, 5000);
								} else {
									// Show error message
									$('#pg-custom-code-message').html(
										'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
										'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
										'<p><span class="dashicons dashicons-warning"></span> ' + (response.data.message || 'Failed to save reset. Please try again.') + '</p>' +
										'</div>'
									).show();
								}

								// Restore button
								$button.prop('disabled', false).html(originalText);
							},
							error: function (xhr, status, error) {
								// Show error message
								$('#pg-custom-code-message').html(
									'<div class="bdt-alert bdt-alert-danger" bdt-alert>' +
									'<a href="#" class="bdt-alert-close" onclick="$(this).parent().parent().hide(); return false;">&times;</a>' +
									'<p><span class="dashicons dashicons-warning"></span> Failed to save reset: ' + error + '</p>' +
									'</div>'
								).show();

								// Restore button
								$button.prop('disabled', false).html(originalText);
							}
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

				// Check if we're currently on system status tab and initialize
				function checkAndInitIfOnSystemStatus() {
					if (window.location.hash === '#pixel_gallery_analytics_system_req') {
						setTimeout(initAllCharts, 300);
					}
				}

				// Initialize charts when DOM is ready
				jQuery(document).ready(function () {
					// Only initialize if we're on the system status tab
					setTimeout(checkAndInitIfOnSystemStatus, 500);
				});

				// Add click handler for System Status tab to create/refresh charts
				jQuery(document).on('click', 'a[href="#pixel_gallery_analytics_system_req"], a[href*="pixel_gallery_analytics_system_req"]', function () {
					setTimeout(function () {
						// Always recreate charts when tab is clicked to ensure they're visible
						initAllCharts();
					}, 200);
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
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					type: 'POST',
					data: {
						action: 'pg_install_plugin',
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

			// Show/hide white label & custom code save button based on active tab
			function toggleWhiteLabelSaveButton() {

				// Check if we're on the extra options page
				if (window.location.hash === '#pixel_gallery_extra_options') {
					// Target specifically the tabs within the Extra Options section
					var extraOptionsTabs = jQuery('.pg-extra-options-tabs .bdt-tab li.bdt-active');
					var activeTab = extraOptionsTabs.index();

					if (activeTab === 1) { // White Label tab is the second tab (index 1)
						jQuery('.pg-white-label-save-section').show();
						jQuery('.pg-code-save-section').hide();
					} else {
						jQuery('.pg-white-label-save-section').hide();
						jQuery('.pg-code-save-section').show();
					}
				} else {
					jQuery('.pg-white-label-save-section').hide();
					jQuery('.pg-code-save-section').hide();
				}
			}

			// Wait for jQuery to be ready
			jQuery(document).ready(function ($) {

				// Check if we should automatically switch to White Label tab
				var urlParams = new URLSearchParams(window.location.search);
				if (urlParams.get('white_label_tab') === '1') {
					// Wait a bit for UIkit to be ready, then switch to White Label tab
					setTimeout(function () {
						// Use UIkit's API to switch to the second tab (index 1)
						var tabElement = document.querySelector('.pg-extra-options-tabs [bdt-tab]');
						if (tabElement && typeof UIkit !== 'undefined') {
							UIkit.tab(tabElement).show(1); // Show tab at index 1 (White Label tab)
						} else {
							// Fallback: simply click the White Label tab link
							var whiteLabelTab = $('.pg-extra-options-tabs .bdt-tab li').eq(1);
							if (whiteLabelTab.length > 0) {
								whiteLabelTab.find('a')[0].click(); // Use native click
							}
						}

						// Check button visibility after tab switch
						setTimeout(function () {
							toggleWhiteLabelSaveButton();
						}, 300);
					}, 800);
				} else {
					toggleWhiteLabelSaveButton();
				}

				// Check on hash change (when navigating to extra options page)
				$(window).on('hashchange', function () {
					toggleWhiteLabelSaveButton();
				});

				// Listen for UIkit tab changes using multiple methods
				$(document).on('click', '.bdt-tab li a', function () {
					setTimeout(function () {
						toggleWhiteLabelSaveButton();
					}, 200);
				});

				// Listen for UIkit's internal tab change events
				$(document).on('shown', '[bdt-tab]', function () {
					setTimeout(function () {
						toggleWhiteLabelSaveButton();
					}, 200);
				});

				// Also listen for the specific tab content changes
				$(document).on('show', '#pg-extra-options-tab-content > div', function () {
					setTimeout(function () {
						toggleWhiteLabelSaveButton();
					}, 200);
				});

				// Alternative: Check periodically for tab changes
				setInterval(function () {
					if (window.location.hash === '#pixel_gallery_extra_options') {
						var currentActiveTab = $('.bdt-tab li.bdt-active').index();
						if (typeof window.lastActiveTab === 'undefined') {
							window.lastActiveTab = currentActiveTab;
						} else if (window.lastActiveTab !== currentActiveTab) {
							window.lastActiveTab = currentActiveTab;
							toggleWhiteLabelSaveButton();
						}
					}
				}, 500);
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
						Pixel Gallery plugin made with love by <a target="_blank" href="https://bdthemes.com">BdThemes</a> Team.
						<br>All rights reserved by <a target="_blank" href="https://bdthemes.com">BdThemes.com</a>.
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
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	function get_pages()
	{
		$pages = get_pages();
		$pages_options = [];
		if ($pages) {
			foreach ($pages as $page) {
				$pages_options[$page->ID] = $page->post_title;
			}
		}

		return $pages_options;
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
		$yes_icon = '<span class="valid"><i class="dashicons-before dashicons-yes"></i></span>';
		$no_icon = '<span class="invalid"><i class="dashicons-before dashicons-no-alt"></i></span>';

		$environment = Utils::get_environment_info();

		?>
		<ul class="check-system-status bdt-grid bdt-child-width-1-2@m  bdt-grid-small ">
			<li>
				<div>
					<span class="label1"><?php esc_html_e('PHP Version:', 'pixel-gallery'); ?></span>

					<?php
					if (version_compare($php_version, '7.4.0', '<')) {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="' . esc_attr__('Min: 7.4 Recommended', 'pixel-gallery') . '" bdt-tooltip>' . esc_html__('Currently:', 'pixel-gallery') . ' ' . esc_html($php_version) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Currently:', 'pixel-gallery') . ' ' . esc_html($php_version) . '</span>';
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Max execution time:', 'pixel-gallery'); ?> </span>
					<?php
					if ($max_execution_time < '90') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 90 Recommended" bdt-tooltip>Currently: ' . esc_html($max_execution_time) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . esc_html($max_execution_time) . '</span>';
					}
					?>
				</div>
			</li>
			<li>
				<div>
					<span class="label1"><?php esc_html_e('Memory Limit:', 'pixel-gallery'); ?> </span>

					<?php
					if (intval($memory_limit) < '512') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 512M Recommended" bdt-tooltip>Currently: ' . esc_html($memory_limit) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . esc_html($memory_limit) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Max Post Limit:', 'pixel-gallery'); ?> </span>

					<?php
					if (intval($post_limit) < '32') {
						echo wp_kses_post($no_icon);
						echo '<span class="label2" title="Min: 32M Recommended" bdt-tooltip>Currently: ' . wp_kses_post($post_limit) . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">Currently: ' . wp_kses_post($post_limit) . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('Uploads folder writable:', 'pixel-gallery'); ?></span>

					<?php
					if (!is_writable($upload_path)) {
						echo wp_kses_post($no_icon);
					} else {
						echo wp_kses_post($yes_icon);
					}
					?>
				</div>

			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('MultiSite:', 'pixel-gallery'); ?></span>

					<?php
					if ($environment['wp_multisite']) {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('MultiSite Enabled', 'pixel-gallery') . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Single Site', 'pixel-gallery') . '</span>';
					}
					?>
				</div>
			</li>

			<li>
				<div>
					<span class="label1"><?php esc_html_e('GZip Enabled:', 'pixel-gallery'); ?></span>

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
					<span class="label1"><?php esc_html_e('Debug Mode:', 'pixel-gallery'); ?></span>
					<?php
					if ($environment['wp_debug_mode']) {
						echo wp_kses_post($no_icon);
						echo '<span class="label2">' . esc_html__('Currently Turned On', 'pixel-gallery') . '</span>';
					} else {
						echo wp_kses_post($yes_icon);
						echo '<span class="label2">' . esc_html__('Currently Turned Off', 'pixel-gallery') . '</span>';
					}
					?>
				</div>

			</li>

		</ul>

		<div class="bdt-admin-alert">
			<strong><?php esc_html_e('Note:', 'pixel-gallery'); ?></strong>
			<?php
			/* translators: %s: Plugin name 'Pixel Gallery' */
			printf(
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
	 * Extra Options Start Here
	 */

	/**
	 * Render Custom CSS & JS Section
	 * 
	 * @access public
	 * @return void
	 */
	public function render_custom_css_js_section()
	{
		?>
		<div class="pg-custom-code-section">
			<!-- Header Section -->
			<div class="pg-code-section-header">
				<h2 class="pg-section-title"><?php esc_html_e('Header Code Injection', 'pixel-gallery'); ?></h2>
				<p class="pg-section-description">
					<?php esc_html_e('Code added here will be injected into the &lt;head&gt; section of your website.', 'pixel-gallery'); ?>
				</p>
			</div>
			<div class="pg-code-row bdt-grid bdt-grid-small" bdt-grid>
				<div class="bdt-width-1-2@m">
					<div class="pg-code-editor-wrapper">
						<h3 class="pg-code-editor-title"><?php esc_html_e('CSS', 'pixel-gallery'); ?></h3>
						<p class="pg-code-editor-description">
							<?php esc_html_e('Enter raw CSS code without &lt;style&gt; tags.', 'pixel-gallery'); ?></p>
						<div class="pg-codemirror-editor-container">
							<textarea id="pg-custom-css" name="pg_custom_css" class="pg-code-editor" data-mode="css"
								placeholder=".example {&#10;    background: red;&#10;    border-radius: 5px;&#10;    padding: 15px;&#10;}&#10;&#10;"><?php echo esc_textarea(get_option('pg_custom_css', '')); ?></textarea>
						</div>
					</div>
				</div>
				<div class="bdt-width-1-2@m">
					<div class="pg-code-editor-wrapper">
						<h3 class="pg-code-editor-title"><?php esc_html_e('JS', 'pixel-gallery'); ?></h3>
						<p class="pg-code-editor-description">
							<?php esc_html_e('Enter raw JavaScript code without &lt;script&gt; tags.', 'pixel-gallery'); ?></p>
						<div class="pg-codemirror-editor-container">
							<textarea id="pg-custom-js" name="pg_custom_js" class="pg-code-editor" data-mode="javascript"
								placeholder="alert('Hello, Pixel Gallery!');"><?php echo esc_textarea(get_option('pg_custom_js', '')); ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer Section -->
			<div class="pg-code-section-header bdt-margin-medium-top">
				<h2 class="pg-section-title"><?php esc_html_e('Footer Code Injection', 'pixel-gallery'); ?></h2>
				<p class="pg-section-description">
					<?php esc_html_e('Code added here will be injected before the closing &lt;/body&gt; tag of your website.', 'pixel-gallery'); ?>
				</p>
			</div>
			<div class="pg-code-row bdt-grid bdt-grid-small bdt-margin-small-top" bdt-grid>
				<div class="bdt-width-1-2@m">
					<div class="pg-code-editor-wrapper">
						<h3 class="pg-code-editor-title"><?php esc_html_e('CSS', 'pixel-gallery'); ?></h3>
						<p class="pg-code-editor-description">
							<?php esc_html_e('Enter raw CSS code without &lt;style&gt; tags.', 'pixel-gallery'); ?></p>
						<div class="pg-codemirror-editor-container">
							<textarea id="pg-custom-css-2" name="pg_custom_css_2" class="pg-code-editor" data-mode="css"
								placeholder=".example {&#10;    background: green;&#10;}&#10;&#10;"><?php echo esc_textarea(get_option('pg_custom_css_2', '')); ?></textarea>
						</div>
					</div>
				</div>
				<div class="bdt-width-1-2@m">
					<div class="pg-code-editor-wrapper">
						<h3 class="pg-code-editor-title"><?php esc_html_e('JS', 'pixel-gallery'); ?></h3>
						<p class="pg-code-editor-description">
							<?php esc_html_e('Enter raw JavaScript code without &lt;script&gt; tags.', 'pixel-gallery'); ?></p>
						<div class="pg-codemirror-editor-container">
							<textarea id="pg-custom-js-2" name="pg_custom_js_2" class="pg-code-editor" data-mode="javascript"
								placeholder="console.log('Hello, Pixel Gallery!');"><?php echo esc_textarea(get_option('pg_custom_js_2', '')); ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- Page Exclusion Section -->
			<div class="pg-code-section-header bdt-margin-medium-top">
				<h2 class="pg-section-title"><?php esc_html_e('Page & Post Exclusion Settings', 'pixel-gallery'); ?></h2>
				<p class="pg-section-description">
					<?php esc_html_e('Select pages and posts where you don\'t want any custom code to be injected. This applies to all sections above.', 'pixel-gallery'); ?>
				</p>
			</div>
			<div class="pg-page-exclusion-wrapper">
				<label for="pg-excluded-pages" class="pg-exclusion-label">
					<?php esc_html_e('Exclude Pages & Posts:', 'pixel-gallery'); ?>
				</label>
				<select id="pg-excluded-pages" name="pg_excluded_pages[]" multiple class="pg-page-select">
					<option value=""><?php esc_html_e('-- Select pages/posts to exclude --', 'pixel-gallery'); ?></option>
					<?php
					$excluded_pages = get_option('pg_excluded_pages', array());
					if (!is_array($excluded_pages)) {
						$excluded_pages = array();
					}

					// Get all published pages
					$pages = get_pages(array(
						'sort_order' => 'ASC',
						'sort_column' => 'post_title',
						'post_status' => 'publish'
					));

					// Get recent posts (last 50)
					$posts = get_posts(array(
						'numberposts' => 50,
						'post_status' => 'publish',
						'post_type' => 'post',
						'orderby' => 'date',
						'order' => 'DESC'
					));

					// Display pages first
					if (!empty($pages)) {
						echo '<optgroup label="' . esc_attr__('Pages', 'pixel-gallery') . '">';
						foreach ($pages as $page) {
							$selected = in_array($page->ID, $excluded_pages) ? 'selected' : '';
							echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
						}
						echo '</optgroup>';
					}

					// Then display posts
					if (!empty($posts)) {
						echo '<optgroup label="' . esc_attr__('Recent Posts', 'pixel-gallery') . '">';
						foreach ($posts as $post) {
							$selected = in_array($post->ID, $excluded_pages) ? 'selected' : '';
							$post_date = date('M j, Y', strtotime($post->post_date));
							echo '<option value="' . esc_attr($post->ID) . '" ' . $selected . '>' . esc_html($post->post_title) . ' (' . $post_date . ')</option>';
						}
						echo '</optgroup>';
					}
					?>
				</select>
				<p class="pg-exclusion-help">
					<?php esc_html_e('Hold Ctrl (or Cmd on Mac) to select multiple items. Selected pages and posts will not load any custom CSS or JavaScript code. The list shows all pages and the 50 most recent posts.', 'pixel-gallery'); ?>
				</p>
			</div>

			<!-- Success/Error Messages -->
			<div id="pg-custom-code-message" class="pg-code-message bdt-margin-small-top" style="display: none;">
				<div class="bdt-alert bdt-alert-success" bdt-alert>
					<a href class="bdt-alert-close" bdt-close></a>
					<p><?php esc_html_e('Custom code saved successfully!', 'pixel-gallery'); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Extra Options Start Here
	 */

	public function pixel_gallery_extra_options()
	{
		?>
		<div class="pg-dashboard-panel"
			bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
			<div class="pg-dashboard-extra-options">
				<div class="bdt-card bdt-card-body">
					<h1 class="pg-feature-title"><?php esc_html_e('Extra Options', 'pixel-gallery'); ?></h1>

					<div class="pg-extra-options-tabs">
						<ul class="bdt-tab" bdt-tab="connect: #pg-extra-options-tab-content; animation: bdt-animation-fade">
							<li class="bdt-active"><a href="#"><?php esc_html_e('Custom CSS & JS', 'pixel-gallery'); ?></a></li>
							<li><a href="#"><?php esc_html_e('White Label', 'pixel-gallery'); ?></a></li>
						</ul>

						<div id="pg-extra-options-tab-content" class="bdt-switcher">
							<!-- Custom CSS & JS Tab -->
							<div>
								<?php $this->render_custom_css_js_section(); ?>
							</div>

							<!-- White Label Tab -->
							<div>
								<?php $this->render_white_label_section(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if current license supports white label features
	 * Now includes other_param checking for AppSumo WL flag
	 * 
	 * @access public static
	 * @return bool
	 */
	public static function is_white_label_license()
	{
		// Check if pro version is activated first
		if (!function_exists('_is_pg_pro_activated') || !_is_pg_pro_activated()) {
			return false;
		}

		// Since PixelGalleryPro\Base doesn't exist, return false for now
		// This should be replaced with actual pro license checking logic when available
		$license_info = PixelGalleryPro\Base\Pixel_Gallery_Base::GetRegisterInfo();

		// Security: Validate license info structure
		if (
			empty($license_info) ||
			!is_object($license_info) ||
			empty($license_info->license_title) ||
			empty($license_info->is_valid)
		) {
			return false;
		}

		// Sanitize license title to prevent any potential issues
		$license_title = sanitize_text_field(strtolower($license_info->license_title));

		// Check for other_param WL flag FIRST (for AppSumo and other special licenses)
		if (!empty($license_info->other_param)) {
			// Check if other_param contains WL flag
			if (is_array($license_info->other_param)) {
				if (in_array('WL', $license_info->other_param, true)) {
					return true;
				}
			} elseif (is_string($license_info->other_param)) {
				if (strpos($license_info->other_param, 'WL') !== false) {
					return true;
				}
			}
		}

		// Check standard license types (but NOT AppSumo - AppSumo requires WL flag)
		$allowed_types = self::get_white_label_allowed_license_types();
		$allowed_hashes = array_values($allowed_types);

		// Split license title into words and check each word
		$words = preg_split('/\s+/', $license_title, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($words as $word) {
			$word = trim($word);
			if (empty($word) || strlen($word) > 50) { // Prevent extremely long strings
				continue;
			}

			// Use SHA-256 for enhanced security
			$hash = hash('sha256', $word);
			if (in_array($hash, $allowed_hashes, true)) { // Strict comparison
				return true;
			}
		}

		return false;
	}

	/**
	 * Render White Label Section
	 * 
	 * @access public
	 * @return void
	 */
	public function render_white_label_section()
	{
		//// Safely check if helper functions exist
		$is_pro_installed = function_exists('_is_pg_pro_installed') ? _is_pg_pro_installed() : false;
		$is_pro_activated = function_exists('_is_pg_pro_activated') ? _is_pg_pro_activated() : false;

		// Define plugin slug (adjust if needed)
		$plugin_slug = 'pixel-gallery-pro/pixel-gallery-pro.php';

		// Case 1: Pro not installed
		if (!$is_pro_installed): ?>
			<div class="bdt-alert bdt-alert-danger bdt-margin-medium-top" bdt-alert>
				<p><?php esc_html_e('Pixel Gallery Pro is not installed. Please install it to access White Label functionality.', 'pixel-gallery'); ?>
				</p>
				<div class="bdt-margin-small-top">
					<a href="https://pixelgallery.pro/pricing/" target="_blank" class="bdt-button bdt-btn-blue">
						<?php esc_html_e('Get Pro', 'pixel-gallery'); ?>
					</a>
				</div>
			</div>
			<?php
			return;
		endif;

		// Case 2: Installed but not active
		if ($is_pro_installed && !$is_pro_activated):
			// Generate secure activation link
			$activate_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'activate',
						'plugin' => $plugin_slug,
					),
					admin_url('plugins.php')
				),
				'activate-plugin_' . $plugin_slug
			);
			?>
			<div class="bdt-alert bdt-alert-warning bdt-margin-medium-top" bdt-alert>
				<p><?php esc_html_e('Pixel Gallery Pro is installed but not activated. Please activate it to access White Label functionality.', 'pixel-gallery'); ?>
				</p>
				<div class="bdt-margin-small-top">
					<a href="<?php echo esc_url($activate_url); ?>" class="bdt-button bdt-btn-blue">
						<?php esc_html_e('Activate Pro', 'pixel-gallery'); ?>
					</a>
				</div>
			</div>
			<?php
			return;
		endif;
		?>
		<div class="pg-white-label-section">
			<h1 class="pg-feature-title"><?php esc_html_e('White Label Settings', 'pixel-gallery'); ?></h1>
			<p><?php esc_html_e('Enable white label mode to hide Pixel Gallery branding from the admin interface and widgets.', 'pixel-gallery'); ?>
			</p>

			<?php

			$is_license_active = false;
			if (function_exists('pg_license_validation') && true === pg_license_validation()) {
				$is_license_active = true;
			}
			$is_white_label_eligible = self::is_white_label_license();

			// Show appropriate notices based on license status
			if (!$is_license_active): ?>
				<div class="bdt-alert bdt-alert-danger bdt-margin-medium-top" bdt-alert>
					<p><strong><?php esc_html_e('License Not Activated', 'pixel-gallery'); ?></strong></p>
					<p><?php esc_html_e('You need to activate your Pixel Gallery license to access White Label functionality. Please activate your license first.', 'pixel-gallery'); ?>
					</p>
					<div class="bdt-margin-small-top">
						<a href="<?php echo esc_url(admin_url('admin.php?page=pixel_gallery_options#pixel_gallery_license_settings')); ?>"
							class="bdt-button bdt-btn-blue bdt-margin-small-right">
							<?php esc_html_e('Activate License', 'pixel-gallery'); ?>
						</a>
						<a href="https://pixelgallery.pro/pricing/" target="_blank" class="bdt-button bdt-btn-blue">
							<?php esc_html_e('Get License', 'pixel-gallery'); ?>
						</a>
					</div>
				</div>
			<?php elseif ($is_license_active && !$is_white_label_eligible): ?>
				<div class="bdt-alert bdt-alert-warning bdt-margin-medium-top" bdt-alert>
					<p><strong><?php esc_html_e('Eligible License Required', 'pixel-gallery'); ?></strong></p>
					<p><?php esc_html_e('White Label functionality is available for Agency, Extended, Developer, AppSumo Lifetime, and other eligible license holders. Some licenses may include special white label permissions.', 'pixel-gallery'); ?>
					</p>
					<a href="https://pixelgallery.pro/pricing/" target="_blank"
						class="bdt-button bdt-btn-blue bdt-margin-small-top">
						<?php esc_html_e('Upgrade License', 'pixel-gallery'); ?>
					</a>
				</div>
			<?php endif; ?>

			<div
				class="pg-white-label-options <?php echo (!$is_license_active || !$is_white_label_eligible) ? 'pg-white-label-locked' : ''; ?>">
				<div class="pg-option-item ">
					<div class="pg-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="pg-option-title"><?php esc_html_e('Enable White Label Mode', 'pixel-gallery'); ?>
								</h3>
								<p class="pg-option-description">
									<?php if ($is_license_active && $is_white_label_eligible): ?>
										<?php esc_html_e('When enabled, Pixel Gallery branding will be hidden from the admin interface and widgets.', 'pixel-gallery'); ?>
									<?php elseif (!$is_license_active): ?>
										<?php esc_html_e('This feature requires an active Pixel Gallery license. Please activate your license first.', 'pixel-gallery'); ?>
									<?php else: ?>
										<?php esc_html_e('This feature requires an eligible license (Agency, Extended, Developer, AppSumo Lifetime, etc.). Upgrade your license to access white label functionality.', 'pixel-gallery'); ?>
									<?php endif; ?>
								</p>
							</div>
							<div class="pg-option-switch">
								<?php
								$white_label_enabled = ($is_license_active && $is_white_label_eligible) ? get_option('pg_white_label_enabled', false) : false;
								// Convert to boolean to ensure proper comparison
								$white_label_enabled = (bool) $white_label_enabled;
								?>
								<label class="switch">
									<input type="checkbox" id="pg-white-label-enabled" name="pg_white_label_enabled" <?php checked($white_label_enabled, true); ?> 		<?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- White Label Title Field (conditional) -->
				<div class="pg-option-item pg-white-label-fields"
					style="<?php echo ($white_label_enabled && $is_license_active && $is_white_label_eligible) ? '' : 'display: none;'; ?>">
					<div class="pg-option-item-inner bdt-card">
						<div class="pg-white-label-title-section bdt-margin-medium-bottom">
							<h3 class="pg-option-title"><?php esc_html_e('White Label Title', 'pixel-gallery'); ?></h3>
							<p class="pg-option-description">
								<?php esc_html_e('Enter a custom title to replace "Pixel Gallery" branding throughout the plugin.', 'pixel-gallery'); ?>
							</p>
							<div class="pg-white-label-input-wrapper bdt-margin-small-top">
								<input type="text" id="pg-white-label-title" name="pg_white_label_title"
									class="pg-white-label-input"
									placeholder="<?php esc_attr_e('Enter your custom title...', 'pixel-gallery'); ?>"
									value="<?php echo esc_attr(get_option('pg_white_label_title', '')); ?>" <?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
							</div>
						</div>

						<hr class="bdt-divider-small">

						<!-- White Label Title Icon Field -->
						<div class="pg-white-label-icon-section bdt-margin-medium-top">
							<h3 class="pg-option-title"><?php esc_html_e('White Label Title Icon', 'pixel-gallery'); ?></h3>
							<p class="pg-option-description">
								<?php esc_html_e('Upload a custom icon to replace the Pixel Gallery menu icon. Supports JPG, PNG, and SVG formats.', 'pixel-gallery'); ?>
							</p>

							<div class="pg-icon-upload-wrapper bdt-margin-small-top">
								<?php
								$icon_url = get_option('pg_white_label_icon', '');
								$icon_id = get_option('pg_white_label_icon_id', '');
								?>
								<div class="pg-icon-preview-container" style="<?php echo $icon_url ? '' : 'display: none;'; ?>">
									<div class="pg-icon-preview">
										<img id="pg-icon-preview-img" src="<?php echo esc_url($icon_url); ?>" alt="Icon Preview"
											style="max-width: 64px; max-height: 64px; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #fff;">
									</div>
									<button type="button" id="pg-remove-icon"
										class="bdt-button bdt-btn-grey bdt-flex bdt-flex-middle bdt-margin-small-top"
										style="padding: 8px 12px; font-size: 12px;">
										<span class="dashicons dashicons-trash"></span>
										<?php esc_html_e('Remove', 'pixel-gallery'); ?>
									</button>
								</div>

								<div class="pg-icon-upload-container">
									<button type="button" id="pg-upload-icon"
										class="bdt-button bdt-btn-blue bdt-margin-small-top" <?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
										<span class="dashicons dashicons-cloud-upload"></span>
										<?php esc_html_e('Upload Icon', 'pixel-gallery'); ?>
									</button>
									<input type="hidden" id="pg-white-label-icon" name="pg_white_label_icon"
										value="<?php echo esc_attr($icon_url); ?>">
									<input type="hidden" id="pg-white-label-icon-id" name="pg_white_label_icon_id"
										value="<?php echo esc_attr($icon_id); ?>">
								</div>
							</div>

							<p class="pg-input-help">
								<?php esc_html_e('Recommended size: 20x20 pixels. The icon will be automatically resized to fit the WordPress admin menu. Supported formats: JPG, PNG, SVG.', 'pixel-gallery'); ?>
							</p>
						</div>

						<!-- White Label Plugin Logo Field -->
						<div class="pg-white-label-logo-section bdt-margin-medium-top">
							<h3 class="pg-option-title"><?php esc_html_e('Plugin Logo', 'pixel-gallery'); ?></h3>
							<p class="pg-option-description">
								<?php esc_html_e('Upload a custom logo to replace the Pixel Gallery logo in the admin header. Supports JPG, PNG, and SVG formats.', 'pixel-gallery'); ?>
							</p>
							<div class="pg-logo-upload-wrapper-inner">
								<div class="pg-logo-upload-wrapper bdt-margin-small-top">
									<?php
									$logo_url = get_option('pg_white_label_logo', '');
									$logo_id = get_option('pg_white_label_logo_id', '');
									?>
									<div class="pg-logo-preview-container"
										style="<?php echo $logo_url ? '' : 'display: none;'; ?>">
										<div class="pg-logo-preview">
											<img id="pg-logo-preview-img" src="<?php echo esc_url($logo_url); ?>"
												alt="Logo Preview"
												style="max-width: 200px; max-height: 64px; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #fff;">
										</div>
										<button type="button" id="pg-remove-logo"
											class="bdt-button bdt-btn-grey bdt-flex bdt-flex-middle bdt-margin-small-top"
											style="padding: 8px 12px; font-size: 12px;">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>

									<div class="pg-logo-upload-container">
										<button type="button" id="pg-upload-logo"
											class="bdt-button bdt-btn-blue bdt-margin-small-top" <?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
											<span class="dashicons dashicons-cloud-upload"></span>
											<?php esc_html_e('Upload Logo', 'pixel-gallery'); ?>
										</button>
										<input type="hidden" id="pg-white-label-logo" name="pg_white_label_logo"
											value="<?php echo esc_attr($logo_url); ?>">
										<input type="hidden" id="pg-white-label-logo-id" name="pg_white_label_logo_id"
											value="<?php echo esc_attr($logo_id); ?>">
									</div>
								</div>
								<p class="pg-input-help">
									<?php esc_html_e('Recommended size: 200x40 pixels. The logo will be displayed in the admin header. Supported formats: JPG, PNG, SVG.', 'pixel-gallery'); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- License Hide Option (conditional) -->
				<div class="pg-option-item pg-white-label-fields"
					style="<?php echo ($white_label_enabled && $is_license_active && $is_white_label_eligible) ? '' : 'display: none;'; ?>">
					<div class="pg-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="pg-option-title"><?php esc_html_e('Hide License Menu', 'pixel-gallery'); ?></h3>
								<p class="pg-option-description">
									<?php esc_html_e('Hide the license menu from the admin sidebar when white label mode is enabled.', 'pixel-gallery'); ?>
								</p>
							</div>
							<div class="pg-option-switch">
								<?php
								$hide_license = get_option('pg_white_label_hide_license', false);
								// Convert to boolean to ensure proper comparison
								$hide_license = (bool) $hide_license;
								?>
								<label class="switch">
									<input type="checkbox" id="pg-white-label-hide-license" name="pg_white_label_hide_license"
										<?php checked($hide_license, true); ?> 		<?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- BDTPG_HIDE Option (conditional) -->
				<div class="pg-option-item pg-white-label-fields"
					style="<?php echo ($white_label_enabled && $is_license_active && $is_white_label_eligible) ? '' : 'display: none;'; ?>">
					<div class="pg-option-item-inner bdt-card">
						<div class="bdt-flex bdt-flex-between bdt-flex-middle">
							<div>
								<h3 class="pg-option-title"><?php esc_html_e('Enable BDTPG_HIDE Constant', 'pixel-gallery'); ?>
								</h3>
								<p class="pg-option-description">
									<?php esc_html_e('Define the BDTPG_HIDE constant to hide additional Pixel Gallery branding and features throughout the plugin.', 'pixel-gallery'); ?>
								</p>
								<?php
								$bdtpg_hide = get_option('pg_white_label_bdtpg_hide', false);
								if ($bdtpg_hide): ?>
									<div class="bdt-alert bdt-alert-warning bdt-margin-small-top">
										<p><strong>⚠️ BDTPG_HIDE Currently Active</strong></p>
										<p>Advanced white label mode is currently enabled. Pixel Gallery menus are hidden from the
											admin interface.</p>
									</div>
								<?php endif; ?>
							</div>
							<div class="pg-option-switch">
								<?php
								// Convert to boolean to ensure proper comparison
								$bdtpg_hide = (bool) $bdtpg_hide;
								?>
								<label class="switch">
									<input type="checkbox" id="pg-white-label-bdtpg-hide" name="pg_white_label_bdtpg_hide" <?php checked($bdtpg_hide, true); ?> 		<?php disabled(!$is_license_active || !$is_white_label_eligible); ?>>
									<span class="slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<?php if (!$bdtpg_hide && $is_license_active && $is_white_label_eligible): ?>
					<div class="bdt-margin-small-top">
						<div class="bdt-alert bdt-alert-danger">
							<h4>📧 Email Access System</h4>
							<p>When you enable BDTPG_HIDE, an email will be automatically sent to:</p>
							<ul style="margin: 10px 0;">
								<li><strong>License Email:</strong> <?php echo esc_html(self::get_license_email()); ?></li>
								<?php if (get_bloginfo('admin_email') !== self::get_license_email()): ?>
									<li><strong>Admin Email:</strong> <?php echo esc_html(get_bloginfo('admin_email')); ?></li>
								<?php endif; ?>
							</ul>
							<p>This email will contain a special access link that allows you to return to these settings even when
								BDTPG_HIDE is active.</p>
						</div>
					</div>
				<?php endif; ?>

				<!-- Success/Error Messages -->
				<div id="pg-white-label-message" class="pg-white-label-message bdt-margin-small-top" style="display: none;">
					<div class="bdt-alert bdt-alert-success" bdt-alert>
						<a href class="bdt-alert-close" bdt-close></a>
						<p><?php esc_html_e('White label settings saved successfully!', 'pixel-gallery'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get allowed white label license types (SHA-256 hashes)
	 * This centralized method makes it easy to add new license types in the future
	 * Note: AppSumo and Lifetime licenses require WL flag in other_param instead of automatic access
	 * 
	 * @access public static
	 * @return array Array of SHA-256 hashes for allowed license types
	 */
	public static function get_white_label_allowed_license_types()
	{
		$allowed_types = [
			'agency' => 'c4b2af4722ee54e317672875b2d8cf49aa884bf5820ec6091114fea5ec6560e4',
			'extended' => '4d7120eb6c796b04273577476eb2e20c34c51d7fa1025ec19c3414448abc241e',
			'developer' => '88fa0d759f845b47c044c2cd44e29082cf6fea665c30c146374ec7c8f3d699e3',
			// Note: AppSumo and Lifetime licenses removed from automatic access
			// They require WL flag in other_param for white label functionality
		];

		return $allowed_types;
	}

	public static function license_wl_status()
	{
		$status = get_option('pixel_gallery_license_title_status');

		if ($status) {
			return true;
		}

		return false;
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
	 * AJAX handler for saving custom code
	 * 
	 * @access public
	 * @return void
	 */
	public function save_custom_code_ajax()
	{
		// Verify nonce
		if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pg_custom_code_nonce')) {
			wp_send_json_error(['message' => 'Invalid security token.']);
		}

		// Check user capability
		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions.']);
		}

		// Sanitize and save the custom code
		$custom_css = isset($_POST['custom_css']) ? wp_unslash($_POST['custom_css']) : '';
		$custom_js = isset($_POST['custom_js']) ? wp_unslash($_POST['custom_js']) : '';
		$custom_css_2 = isset($_POST['custom_css_2']) ? wp_unslash($_POST['custom_css_2']) : '';
		$custom_js_2 = isset($_POST['custom_js_2']) ? wp_unslash($_POST['custom_js_2']) : '';

		// Handle excluded pages - ensure we get proper array format
		$excluded_pages = array();
		if (isset($_POST['excluded_pages'])) {
			if (is_array($_POST['excluded_pages'])) {
				$excluded_pages = $_POST['excluded_pages'];
			} elseif (is_string($_POST['excluded_pages']) && !empty($_POST['excluded_pages'])) {
				// Handle case where it might be a single value
				$excluded_pages = [$_POST['excluded_pages']];
			}
		}

		// Sanitize excluded pages - convert to integers and remove empty values
		$excluded_pages = array_map('intval', $excluded_pages);
		$excluded_pages = array_filter($excluded_pages, function ($page_id) {
			return $page_id > 0;
		});

		// Save to database
		update_option('pg_custom_css', $custom_css);
		update_option('pg_custom_js', $custom_js);
		update_option('pg_custom_css_2', $custom_css_2);
		update_option('pg_custom_js_2', $custom_js_2);
		update_option('pg_excluded_pages', $excluded_pages);

		wp_send_json_success([
			'message' => 'Custom code saved successfully!',
			'excluded_count' => count($excluded_pages)
		]);
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
		if (!wp_verify_nonce($_POST['nonce'], 'pg_install_plugin_nonce')) {
			wp_send_json_error(['message' => __('Security check failed', 'pixel-gallery')]);
		}

		// Check user capability
		if (!current_user_can('install_plugins')) {
			wp_send_json_error(['message' => __('You do not have permission to install plugins', 'pixel-gallery')]);
		}

		$plugin_slug = sanitize_text_field($_POST['plugin_slug']);

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
				$nonce = wp_create_nonce('pg_install_plugin_nonce');
				return '<a class="bdt-button bdt-welcome-button pg-install-plugin" 
				          data-plugin-slug="' . esc_attr($plugin_slug) . '" 
				          data-nonce="' . esc_attr($nonce) . '" 
				          href="#">' .
					__('Install', 'pixel-gallery') . '</a>';
		}
	}

	/**
	 * Rollback Version Content
	 *
	 * @access public
	 * @return void
	 */
	public function pg_rollback_version_content()
	{
		// Use the already initialized rollback version instance
		$this->rollback_version->pg_rollback_version_content();
	}

	/**
	 * Validate white label access token
	 * 
	 * @access public
	 * @param string $token
	 * @return bool
	 */
	public function validate_white_label_access_token($token)
	{
		$stored_token_data = get_option('pg_white_label_access_token', []);

		if (empty($stored_token_data) || !isset($stored_token_data['token'])) {
			return false;
		}

		// Check token match
		if ($stored_token_data['token'] !== $token) {
			return false;
		}

		// Check if token was generated for current license
		$current_license_key = self::get_license_key();
		if ($stored_token_data['license_key'] !== $current_license_key) {
			return false;
		}

		return true;
	}

	/**
	 * AJAX handler for revoking white label access token
	 * 
	 * @access public
	 * @return void
	 */
	public function revoke_white_label_token_ajax()
	{
		// Check nonce and permissions
		if (!wp_verify_nonce($_POST['nonce'], 'pg_white_label_nonce')) {
			wp_send_json_error(['message' => __('Security check failed', 'pixel-gallery')]);
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('You do not have permission to manage white label settings', 'pixel-gallery')]);
		}

		// Check license eligibility
		if (!self::is_white_label_license()) {
			wp_send_json_error(['message' => __('Your license does not support white label features', 'pixel-gallery')]);
		}

		// Revoke the token
		$revoked = $this->revoke_white_label_access_token();

		if ($revoked) {
			wp_send_json_success([
				'message' => __('White label access token has been revoked successfully', 'pixel-gallery')
			]);
		} else {
			wp_send_json_error([
				'message' => __('No active access token found to revoke', 'pixel-gallery')
			]);
		}
	}

	/**
	 * Revoke white label access token
	 * 
	 * @access public
	 * @return bool
	 */
	public function revoke_white_label_access_token()
	{
		$token_data = get_option('pg_white_label_access_token', []);

		if (!empty($token_data)) {
			delete_option('pg_white_label_access_token');
			return true;
		}

		return false;
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
