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
		add_action( 'wp_ajax_setup_wizard_install_plugins', array( $this, 'install_plugins' ) );
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

        wp_enqueue_style('bdt-uikit', BDTPG_ADMIN_URL . 'assets/css/bdt-uikit'. $direction_suffix .'.css', [], '3.21.7');
		wp_enqueue_script('bdt-uikit', BDTPG_ADMIN_URL . 'assets/js/bdt-uikit.min.js', ['jquery'], '3.21.7', true);

		wp_register_script( 'pg-setup-wizard', plugins_url( 'assets/js/setup-wizard.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_register_style( 'pg-setup-wizard', plugins_url( 'assets/css/setup-wizard.css', __FILE__ ), array(), '1.0.0' );

		wp_enqueue_script( 'pg-setup-wizard' );
		wp_enqueue_style( 'pg-setup-wizard' );

		wp_localize_script(
			'pg-setup-wizard',
			'BDT_SetupWizard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'setup_wizard_nonce' ),
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

	// Install plugins
	public function install_plugins() {
		check_ajax_referer( 'setup_wizard_nonce', 'nonce' );

		$plugin_slugs = isset( $_POST['plugins'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['plugins'] ) ) : array();

		if ( empty( $plugin_slugs ) || ! is_array( $plugin_slugs ) ) {
			wp_send_json_error( array( 'message' => 'Invalid plugins array' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Replace new \Plugin_Installer_Skin with new Quiet_Upgrader_Skin when output needs to be suppressed.
		$skin = new Quiet_Upgrader_Skin();
		// $skin     = new \Plugin_Installer_Skin( array( 'api' => $api ) );
		$upgrader = new \Plugin_Upgrader( $skin );

		// $upgrader = new \Plugin_Upgrader();

        $installedPlugins = get_plugins();
		$results = array();

		foreach ( $plugin_slugs as $plugin_slug ) {
            // skip when the plugin is already active
            if (is_plugin_active($plugin_slug)) {
                $results[] = array(
                    'slug'    => $plugin_slug,
                    'success' => true,
                    'message' => 'Installed and activated successfully',
                );
                continue;
            }

            // Download the plugin if the plugin is not installed
            if (!isset($installedPlugins[$plugin_slug])) {
                $slug = explode('/', $plugin_slug)[0];
                $api = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

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
            }

            // active the plugin
            if ( is_plugin_inactive($plugin_slug) ) {
                $activation_result = activate_plugin( $plugin_slug );
                if ( is_wp_error( $activation_result ) ) {
                    $results[] = array(
                        'slug'    => $slug,
                        'success' => false,
                        'message' => $activation_result->get_error_message(),
                    );
                    continue;
                }

                $results[] = array(
                    'slug'    => $plugin_slug,
                    'success' => true,
                    'message' => 'Installed and activated successfully',
                );
            }
		}

		ob_clean();
		wp_send_json_success( array( 'results' => $results ) );
		wp_die();
	}

	/**
	 * Get the main plugin file path for a given slug.
	 *
	 * @param string $slug Plugin slug.
	 * @return string|false Plugin file path or false if not found.
	 */
	private function get_plugin_file( $slug ) {
		$plugins = get_plugins();

		foreach ( $plugins as $file => $plugin ) {
			if ( strpos( $file, $slug ) !== false ) {
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
