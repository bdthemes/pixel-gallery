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
		add_action( 'wp_ajax_bdtpg_setup_wizard_import_template', array( $this, 'import_template' ) );
		add_action( 'wp_ajax_bdtpg_setup_wizard_import_template_runner', array( $this, 'import_template_runner' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
			<div class="bdt-setup-wizard pg-content-loaded">
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
			<div class="bdt-setup-wizard pg-content-loaded">
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

		// Versioned with the plugin so browsers never pair cached wizard assets with newer markup.
		wp_register_script( 'pg-setup-wizard', plugins_url( 'assets/js/setup-wizard.js', __FILE__ ), array( 'jquery' ), BDTPG_VER, true );
		wp_register_style( 'pg-setup-wizard', plugins_url( 'assets/css/setup-wizard.css', __FILE__ ), array(), BDTPG_VER );

		wp_enqueue_script( 'pg-setup-wizard' );
		wp_enqueue_style( 'pg-setup-wizard' );

		wp_localize_script(
			'pg-setup-wizard',
			'BDT_SetupWizard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bdtpg_setup_wizard_nonce' ),
				'is_fullscreen' => true,
				'i18n'     => array(
					'importing'         => __( 'Importing...', 'pixel-gallery' ),
					/* translators: %s: template name. */
					'importing_template' => __( 'Importing %s...', 'pixel-gallery' ),
					'imported'          => __( 'Imported', 'pixel-gallery' ),
					/* translators: %s: template name. */
					'imported_template' => __( '%s Imported', 'pixel-gallery' ),
					'failed'            => __( 'Failed', 'pixel-gallery' ),
					'import_failed'     => __( 'Import Failed', 'pixel-gallery' ),
					'edit_page'         => __( 'Edit Page', 'pixel-gallery' ),
				),
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
	 * Ready-to-use templates offered on the "Good to Go" step.
	 *
	 * Each template is an Elementor kit bundled in assets/templates/ and listed in
	 * assets/data.json. Templates built on Pixel Gallery Pro widgets are only
	 * offered while Pro is active, so every template shown can be imported.
	 *
	 * The kits ship unpacked, as the plain JSON and XML files Elementor writes
	 * into a kit archive, because wordpress.org does not allow archives inside a
	 * plugin. build_kit_archive() packs the directory back into a .zip when an
	 * import actually runs.
	 *
	 * @return array[] Template data keyed by slug: title, thumbnail, demo_url and dir.
	 */
	public static function get_templates() {
		$assets_path = __DIR__ . '/assets/';
		$data        = wp_json_file_decode( $assets_path . 'data.json', array( 'associative' => true ) );
		$templates   = array();

		foreach ( (array) $data as $template ) {
			$import_path = isset( $template['import_url'] ) ? (string) $template['import_url'] : '';

			if ( '' === $import_path ) {
				continue;
			}

			if ( ! empty( $template['is_pro'] ) && true !== _is_pg_pro_activated() ) {
				continue;
			}

			$slug = sanitize_key( basename( untrailingslashit( $import_path ) ) );
			$dir  = $assets_path . 'templates/' . $slug . '/';

			// A kit without its manifest cannot be imported, so do not offer it.
			if ( '' === $slug || ! is_dir( $dir ) || ! is_file( $dir . 'manifest.json' ) ) {
				continue;
			}

			$templates[ $slug ] = array(
				'title'     => isset( $template['title'] ) ? (string) $template['title'] : $slug,
				'thumbnail' => plugins_url( 'assets/' . ltrim( (string) ( $template['thumbnail'] ?? '' ), '/' ), __FILE__ ),
				'demo_url'  => isset( $template['demo_url'] ) ? (string) $template['demo_url'] : '',
				'dir'       => $dir,
			);
		}

		return $templates;
	}

	/**
	 * Pack a bundled template directory into a kit archive Elementor can read.
	 *
	 * Uses ZipArchive when the host has it and falls back to PclZip, which ships
	 * with WordPress, so the import does not depend on an optional extension.
	 *
	 * @param string $template_dir Absolute path to the unpacked kit.
	 * @return string|WP_Error Path to the archive, or an error.
	 */
	private function build_kit_archive( $template_dir ) {
		$template_dir = trailingslashit( $template_dir );

		if ( ! is_dir( $template_dir ) ) {
			return new \WP_Error( 'pixel_gallery_kit_missing', esc_html__( 'The template is missing from this plugin.', 'pixel-gallery' ) );
		}

		$files = array();

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $template_dir, \FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$files[] = $file->getPathname();
			}
		}

		if ( ! $files ) {
			return new \WP_Error( 'pixel_gallery_kit_empty', esc_html__( 'The template is missing from this plugin.', 'pixel-gallery' ) );
		}

		$archive_path = wp_tempnam( 'pixel-gallery-kit.zip' );

		if ( ! $archive_path ) {
			return new \WP_Error( 'pixel_gallery_kit_tempfile', esc_html__( 'Could not prepare the template for import.', 'pixel-gallery' ) );
		}

		if ( class_exists( '\ZipArchive' ) ) {
			$zip = new \ZipArchive();

			if ( true !== $zip->open( $archive_path, \ZipArchive::OVERWRITE ) ) {
				wp_delete_file( $archive_path );
				return new \WP_Error( 'pixel_gallery_kit_zip', esc_html__( 'Could not prepare the template for import.', 'pixel-gallery' ) );
			}

			foreach ( $files as $file ) {
				// Entry names are relative to the kit root, which is what Elementor expects.
				$zip->addFile( $file, str_replace( $template_dir, '', $file ) );
			}

			$zip->close();

			return $archive_path;
		}

		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

		$archive = new \PclZip( $archive_path );
		$created = $archive->create( implode( ',', $files ), PCLZIP_OPT_REMOVE_PATH, untrailingslashit( $template_dir ) );

		if ( 0 === $created ) {
			wp_delete_file( $archive_path );
			return new \WP_Error( 'pixel_gallery_kit_zip', esc_html__( 'Could not prepare the template for import.', 'pixel-gallery' ) );
		}

		return $archive_path;
	}

	/**
	 * Start importing a bundled template kit with Elementor's kit importer.
	 *
	 * Only a template slug is accepted, never a URL, and it must be one of
	 * get_templates(). Returns the Elementor import session and the runners the
	 * browser then executes one request at a time.
	 */
	public function import_template() {
		check_ajax_referer( 'bdtpg_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to import templates.', 'pixel-gallery' ) ), 403 );
		}

		$slug      = isset( $_POST['template'] ) ? sanitize_key( wp_unslash( $_POST['template'] ) ) : '';
		$templates = self::get_templates();

		if ( '' === $slug || ! isset( $templates[ $slug ] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unknown template.', 'pixel-gallery' ) ) );
		}

		$import_export = $this->get_elementor_import_export();

		if ( ! $import_export ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Elementor\'s kit importer is not available. Please update Elementor and try again.', 'pixel-gallery' ) ) );
		}

		// The kit ships unpacked, so pack it before handing it to Elementor.
		$kit_archive = $this->build_kit_archive( $templates[ $slug ]['dir'] );

		if ( is_wp_error( $kit_archive ) ) {
			wp_send_json_error( array( 'message' => $kit_archive->get_error_message() ) );
		}

		try {
			// Elementor extracts and cleans up the kit it is given, so hand it a
			// temporary copy and drop the archive we just built either way.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads the archive this request just built from files inside this plugin.
			$kit_zip_path = Plugin::$instance->uploads_manager->create_temp_file( file_get_contents( $kit_archive ), 'kit.zip' );

			wp_delete_file( $kit_archive );

			if ( is_wp_error( $kit_zip_path ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Could not prepare the template for import.', 'pixel-gallery' ) ) );
			}

			$upload   = $import_export->upload_kit( $kit_zip_path, 'local' );
			$manifest = ( isset( $upload['manifest'] ) && is_array( $upload['manifest'] ) ) ? $upload['manifest'] : array();

			$missing_plugins = array();

			foreach ( (array) ( $manifest['plugins'] ?? array() ) as $plugin ) {
				if ( empty( $plugin['plugin'] ) || ! is_plugin_active( $plugin['plugin'] . '.php' ) ) {
					$missing_plugins[] = isset( $plugin['name'] ) ? (string) $plugin['name'] : (string) ( $plugin['plugin'] ?? '' );
				}
			}

			if ( $missing_plugins ) {
				wp_send_json_error(
					array(
						/* translators: %s: comma-separated list of plugin names. */
						'message' => sprintf( esc_html__( 'Please activate these plugins first: %s', 'pixel-gallery' ), esc_html( implode( ', ', $missing_plugins ) ) ),
					)
				);
			}

			$include = array();

			foreach ( array( 'templates' => 'templates', 'content' => 'content', 'site-settings' => 'settings' ) as $manifest_key => $part ) {
				if ( isset( $manifest[ $manifest_key ] ) ) {
					$include[] = $part;
				}
			}

			$import = $import_export->import_kit(
				$upload['session'],
				array(
					'id'                      => '',
					'session'                 => $upload['session'],
					'include'                 => $include,
					'overrideConditions'      => array(),
					'selectedCustomPostTypes' => isset( $manifest['custom-post-type-title'] ) ? array_keys( (array) $manifest['custom-post-type-title'] ) : array(),
				),
				true
			);

			// Remember the session so the runner requests can only continue an
			// import this wizard started.
			set_transient(
				self::template_import_key( $import['session'] ),
				array(
					'runners' => array_values( (array) $import['runners'] ),
					'title'   => $templates[ $slug ]['title'],
				),
				HOUR_IN_SECONDS
			);

			wp_send_json_success(
				array(
					'session' => $import['session'],
					'runners' => array_values( (array) $import['runners'] ),
				)
			);
		} catch ( \Throwable $error ) {
			// The archive is normally deleted as soon as Elementor has its own
			// copy; clean it up here too if we failed before reaching that point.
			if ( file_exists( $kit_archive ) ) {
				wp_delete_file( $kit_archive );
			}

			wp_send_json_error( array( 'message' => esc_html__( 'Import failed: ', 'pixel-gallery' ) . esc_html( $error->getMessage() ) ) );
		}
	}

	/**
	 * Run one step ("runner") of a template import started by import_template().
	 */
	public function import_template_runner() {
		check_ajax_referer( 'bdtpg_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to import templates.', 'pixel-gallery' ) ), 403 );
		}

		$session = isset( $_POST['session'] ) ? sanitize_text_field( wp_unslash( $_POST['session'] ) ) : '';
		$runner  = isset( $_POST['runner'] ) ? sanitize_text_field( wp_unslash( $_POST['runner'] ) ) : '';
		$state   = '' !== $session ? get_transient( self::template_import_key( $session ) ) : false;

		if ( ! is_array( $state ) || ! in_array( $runner, (array) $state['runners'], true ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'This import has expired. Please try again.', 'pixel-gallery' ) ) );
		}

		$import_export = $this->get_elementor_import_export();

		if ( ! $import_export ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Elementor\'s kit importer is not available. Please update Elementor and try again.', 'pixel-gallery' ) ) );
		}

		try {
			$result = $import_export->import_kit_by_runner( $session, $runner );

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Elementor's own action, fired after each runner as Elementor does.
			do_action( 'elementor/import-export/import-kit/runner/after-run', $result );
		} catch ( \Throwable $error ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Import failed: ', 'pixel-gallery' ) . esc_html( $error->getMessage() ) ) );
		}

		$response = array( 'runner' => $runner );
		$runners  = (array) $state['runners'];

		if ( end( $runners ) === $runner ) {
			delete_transient( self::template_import_key( $session ) );

			$page_ids = $result['content']['page']['succeed'] ?? array();
			$page_id  = is_array( $page_ids ) ? absint( reset( $page_ids ) ) : 0;

			if ( $page_id && 'page' === get_post_type( $page_id ) ) {
				// Kits keep the placeholder title they were exported with ("Demo 08"),
				// so name the page after the template the user picked.
				if ( ! empty( $state['title'] ) ) {
					wp_update_post(
						array(
							'ID'         => $page_id,
							'post_title' => sanitize_text_field( $state['title'] ),
						)
					);
				}

				$response['edit_url'] = admin_url( 'post.php?post=' . $page_id . '&action=elementor' );
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Transient key that tracks one template import session.
	 *
	 * @param string $session Elementor import session id.
	 * @return string
	 */
	private static function template_import_key( $session ) {
		return 'bdtpg_template_import_' . md5( (string) $session );
	}

	/**
	 * Elementor's kit import/export component, or null when it is unavailable.
	 *
	 * @return object|null
	 */
	private function get_elementor_import_export() {
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) || empty( Plugin::$instance->app ) ) {
			return null;
		}

		$import_export = Plugin::$instance->app->get_component( 'import-export' );

		if ( ! $import_export || ! method_exists( $import_export, 'upload_kit' ) || ! method_exists( $import_export, 'import_kit_by_runner' ) ) {
			return null;
		}

		return $import_export;
	}

}

// Initialize the Setup Wizard
Setup_Wizard::get_instance();
