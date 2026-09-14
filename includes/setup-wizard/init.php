<?php

namespace PixelGallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the Remote Data Handler
require_once __DIR__ . '/class-remote-data-handler.php';

use PixelGallery\Admin\ModuleService;
use Elementor\Plugin;
/**
 * Overwrite the feedback method in the WP_Upgrader_Skin
 * to suppress the normal feedback.
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

class Quiet_Upgrader_Skin extends \WP_Upgrader_Skin {
	/*
	 * Suppress normal upgrader feedback / output
	 */
	public function feedback( $string, ...$args ) {
		/* no output */
	}
}


class Setup_Wizard {

	// Singleton instance
	private static $instance = null;

	// Constructor
	private function __construct() {
		$this->init_hooks();
	}

	// Get instance
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// Initialize hooks
	private function init_hooks() {
		add_action( 'wp_ajax_bdtpg_setup_wizard_install_plugins', array( $this, 'install_plugins' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'activate_default_widgets' ) );
		add_action( 'admin_init', array( $this, 'maybe_display_setup_wizard' ) );
		add_action( 'admin_init', array( $this, 'check_manual_wizard_request' ) );
	}

	// Check for manual wizard requests
	public function check_manual_wizard_request() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only request flag to display the setup wizard, no state change.
		$is_setup_wizard_request = isset($_GET['pg_setup_wizard']) && 'show' === sanitize_text_field(wp_unslash($_GET['pg_setup_wizard']));
		
		if ( $is_setup_wizard_request ) {
			// Use the same approach as first activation - completely override the page
			add_action('admin_head', function() {
				?>
				<style>
					html, body {
						height: 100%;
						margin: 0;
						padding: 0;
						overflow: hidden;
					}
					#wpwrap, #wpcontent, #wpbody, #wpbody-content {
						height: 100%;
						padding: 0;
						margin: 0;
					}
					#adminmenumain, #wpadminbar {
						display: none;
					}
				</style>
				<script>
					jQuery(document).ready(function($) {
						$('body').addClass('bdt-setup-wizard-active');
					});
				</script>
				<?php
				
				// Display setup wizard using the same method as first activation
				$this->display_page();
			});
		}
	}

	// Display wizard in fullscreen mode
	public function display_wizard_fullscreen() {
		?>
		<style>
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
				overflow: hidden;
			}
			#wpwrap, #wpcontent, #wpbody, #wpbody-content {
				height: 100%;
				padding: 0;
				margin: 0;
			}
			#adminmenumain, #wpadminbar {
				display: none;
			}
		</style>
		<?php
		// Directly output the wizard content
		add_action('admin_footer', function() {
			echo '<div id="pg-setup-wizard-container">';
			$this->display_page();
			echo '</div>';
			?>
			<script>
				jQuery(document).ready(function($) {
					$('body').addClass('bdt-setup-wizard-active');
					// Hide all other content and show only our wizard
					$('#wpbody-content').html($('#pg-setup-wizard-container').html());
					$('#pg-setup-wizard-container').remove();
				});
			</script>
			<?php
		}, 999);
	}

	// Get wizard HTML content
	public function get_wizard_html() {
		ob_start();
		?>
		<div class="bdt-setup-wizard-overlay pg-setup-wizard">
			<div class="bdt-setup-wizard content-loaded">
				<?php
				require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/render.php';
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// Check if this is first activation and display setup wizard if needed
	public function maybe_display_setup_wizard() {
		// Only check for first activation here
		if ( get_option( 'bdtpg_setup_wizard_completed' ) === false ) {
			// Set the flag so it doesn't run again
			update_option( 'bdtpg_setup_wizard_completed', true );
			
			// Add a header to ensure proper full-page display
			add_action('admin_head', function() {
				?>
				<style>
					html, body {
						height: 100%;
						margin: 0;
						padding: 0;
						overflow: hidden;
					}
					#wpwrap, #wpcontent, #wpbody, #wpbody-content {
						height: 100%;
						padding: 0;
						margin: 0;
					}
					#adminmenumain, #wpadminbar {
						display: none;
					}
				</style>
				<script>
					jQuery(document).ready(function($) {
						$('body').addClass('bdt-setup-wizard-active');
					});
				</script>
				<?php
				
				// Display setup wizard
				$this->display_page();
			});
		}
	}

	// Keep the admin_menu method for reference but not hooked
	public function admin_menu() {
		add_submenu_page(
			'pixel_gallery_options',
			esc_html__( 'Setup Wizard', 'pixel-gallery' ),
			esc_html__( 'Setup Wizard', 'pixel-gallery' ),
			'manage_options',
			'pixel-gallery-setup-wizard',
			array( $this, 'display_page' )
		);
	}

	public function display_page() {
		?>
		<div class="bdt-setup-wizard-overlay pg-setup-wizard">
			<div class="bdt-setup-wizard content-loaded">
				<?php
				require_once plugin_dir_path( BDTPG__FILE__ ) . 'includes/setup-wizard/views/render.php';
				?>
			</div>
		</div>
		<?php
	}

	// Enqueue necessary scripts
	public function enqueue_scripts() {

        $direction_suffix = is_rtl() ? '.rtl' : '';

        wp_enqueue_style('bdt-uikit', BDTPG_ADMIN_URL . 'assets/css/bdt-uikit'. $direction_suffix .'.css', [], BDTPG_VER);
		wp_enqueue_script('bdt-uikit', BDTPG_ADMIN_URL . 'assets/js/bdt-uikit.min.js', ['jquery'], '3.25.22', true);

		wp_register_script( 'pg-setup-wizard', plugins_url( 'assets/js/setup-wizard.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_register_style( 'pg-setup-wizard', plugins_url( 'assets/css/setup-wizard.css', __FILE__ ), array(), '1.0.0' );

		wp_enqueue_script( 'pg-setup-wizard' );
		wp_enqueue_style( 'pg-setup-wizard' );

		wp_localize_script(
			'pg-setup-wizard',
			'BDT_SetupWizard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bdtpg_setup_wizard_nonce' ),
				'is_fullscreen' => true
			)
		);
	}

	public static function get_widget_map() {
		$arr_obj = ModuleService::get_widget_settings(
			function ( $settings ) {
				$core_widgets = $settings['settings_fields']['pixel_gallery_active_modules'];
				return $core_widgets;
			}
		);
		return $arr_obj;
	}

	/**
	 * Install (and, only with separate explicit consent, activate) the companion
	 * plugins the user ticked in the setup wizard.
	 *
	 * Nothing here runs unattended: every plugin is opt-in (all toggles start
	 * switched off), activation is a second, separate opt-in, and both steps are
	 * gated on the matching capability. Only slugs on the plugin's own
	 * allow-list can be installed or activated.
	 */
	public function install_plugins() {
		check_ajax_referer( 'bdtpg_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'pixel-gallery' ) ), 403 );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$requested = isset( $_POST['plugins'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['plugins'] ) ) : array();
		$allowed   = \PixelGallery\SetupWizard\Remote_Data_Handler::get_plugin_slugs();

		// Normalise "dir/file.php" to the directory slug and keep only slugs this
		// plugin actually offers, so an arbitrary plugin can never be targeted.
		$plugin_slugs = array();
		foreach ( $requested as $raw_slug ) {
			$slug = ( false !== strpos( $raw_slug, '/' ) ) ? dirname( $raw_slug ) : $raw_slug;
			$slug = sanitize_key( $slug );

			if ( '' !== $slug && in_array( $slug, $allowed, true ) && ! in_array( $slug, $plugin_slugs, true ) ) {
				$plugin_slugs[] = $slug;
			}
		}

		if ( empty( $plugin_slugs ) ) {
			wp_send_json_error( array( 'message' => __( 'No valid plugin was selected.', 'pixel-gallery' ) ) );
		}

		/*
		 * Activation is a separate, explicit consent: the user has to tick the
		 * "activate after installing" box in the wizard, and must be allowed to
		 * activate plugins. Otherwise the plugin is only installed and the user
		 * activates it themselves from the Plugins screen.
		 */
		$activation_consent = isset( $_POST['activate'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['activate'] ) );
		$may_activate       = $activation_consent && current_user_can( 'activate_plugins' );

		// Replace new \Plugin_Installer_Skin with new Quiet_Upgrader_Skin when output needs to be suppressed.
		$skin     = new Quiet_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		$results = array();

		foreach ( $plugin_slugs as $plugin_slug ) {
			$plugin_file = $this->get_plugin_file( $plugin_slug );

			// Already active: nothing to do.
			if ( $plugin_file && is_plugin_active( $plugin_file ) ) {
				$results[] = array(
					'slug'      => $plugin_slug,
					'success'   => true,
					'activated' => true,
					'message'   => __( 'Already installed and active.', 'pixel-gallery' ),
				);
				continue;
			}

			// Not installed yet: download it from WordPress.org.
			if ( ! $plugin_file ) {
				$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

				if ( is_wp_error( $api ) ) {
					$results[] = array(
						'slug'    => $plugin_slug,
						'success' => false,
						'message' => $api->get_error_message(),
					);
					continue;
				}

				$result = $upgrader->install( $api->download_link );

				if ( is_wp_error( $result ) ) {
					$results[] = array(
						'slug'    => $plugin_slug,
						'success' => false,
						'message' => $result->get_error_message(),
					);
					continue;
				}

				if ( true !== $result ) {
					$results[] = array(
						'slug'    => $plugin_slug,
						'success' => false,
						'message' => __( 'Installation failed. Please install this plugin from the Plugins screen.', 'pixel-gallery' ),
					);
					continue;
				}

				wp_clean_plugins_cache( false );
				$plugin_file = $this->get_plugin_file( $plugin_slug );
			}

			if ( ! $plugin_file ) {
				$results[] = array(
					'slug'    => $plugin_slug,
					'success' => false,
					'message' => __( 'The plugin was downloaded but could not be located afterwards.', 'pixel-gallery' ),
				);
				continue;
			}

			// Installed. Activate only if the user explicitly asked us to.
			if ( ! $may_activate ) {
				$results[] = array(
					'slug'      => $plugin_slug,
					'success'   => true,
					'activated' => false,
					'message'   => __( 'Installed. You can activate it from the Plugins screen.', 'pixel-gallery' ),
				);
				continue;
			}

			$activation_result = activate_plugin( $plugin_file );

			if ( is_wp_error( $activation_result ) ) {
				$results[] = array(
					'slug'      => $plugin_slug,
					'success'   => true,
					'activated' => false,
					'message'   => $activation_result->get_error_message(),
				);
				continue;
			}

			$results[] = array(
				'slug'      => $plugin_slug,
				'success'   => true,
				'activated' => true,
				'message'   => __( 'Installed and activated.', 'pixel-gallery' ),
			);
		}

		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Get the main plugin file path for a given slug.
	 *
	 * @param string $slug Plugin slug.
	 * @return string|false Plugin file path or false if not found.
	 */
	private function get_plugin_file( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( get_plugins() ) as $file ) {
			// Compare the directory exactly: a substring match would happily
			// return "ultimate-post-kit-pro" for the slug "ultimate-post-kit".
			if ( dirname( $file ) === $slug ) {
				return $file;
			}
		}

		return false;
	}
    
    /**
     * Activate default widgets in setup wizard
     */
    public function activate_default_widgets() {
        // List of widgets to activate by default
        $default_active_widgets = array(
            'accordion',
            'advanced-button',
            'advanced-heading',
            'advanced-icon-box',
            'advanced-image-gallery',
            'audio-player',
            'brand-grid',
            'call-out',
            'carousel',
            'custom-gallery',
            'custom-carousel',
            'contact-form',
            'dropbar',
            'iconnav',
            'lightbox',
            'modal',
            'member',
            'navbar',
            'price-list',
            'price-table',
            'panel-slider',
            'slider',
            'post-grid',
            'post-list',
            'product-grid',
            'search',
            'scroll-button',
            'social-share',
            'tabs',
            'trailer-box',
            'user-login'
        );
        
        // Get current active modules
        $active_modules = get_option('pixel_gallery_active_modules', array());
        
        // Make sure $active_modules is an array
        if (!is_array($active_modules)) {
            $active_modules = array();
        }
        
        // Check if active_modules option exists and is not empty
        // If it's a new installation or option doesn't exist, we'll set our defaults
        $modified = false;
        
        foreach ($default_active_widgets as $widget) {
            // Only set if not already defined (prevents overriding user settings on existing installations)
            if (!isset($active_modules[$widget])) {
                $active_modules[$widget] = 'on';
                $modified = true;
            }
        }
        
        // Update the option if changes were made
        if ($modified) {
            update_option('pixel_gallery_active_modules', $active_modules);
        }
    }
}

// Initialize the Setup Wizard
Setup_Wizard::get_instance();
