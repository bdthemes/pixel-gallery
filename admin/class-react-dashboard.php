<?php
/**
 * Pixel Gallery — React admin dashboard.
 *
 * Replaces the body of the Pixel Gallery dashboard page with a React app
 * built from src/admin/app (output in build/admin), laid out like the Element
 * Pack, Prime Slider and Ultimate Post Kit dashboards: app bar, tab strip,
 * cards.
 *
 * Nothing new is exposed to the network: settings are saved through the
 * existing nonce- and capability-checked `pixel_gallery_settings_save`
 * AJAX action, and every value the app shows is passed in once, on page load.
 *
 * Panels that add-ons render in PHP — the tabs registered through
 * `pixel_gallery/admin/settings_sections` and printed on
 * `pixel_gallery/admin/settings_pages`, and the License page (Pixel Gallery
 * Pro: Extra Options, Rollback Version, License) — are still rendered here,
 * hidden, and moved into the app's matching route, so their own markup,
 * scripts and forms keep working unchanged. An add-on can instead hand the
 * app the data for a native page through `pixel_gallery/admin/react_pages`.
 * Other Plugins is rendered by the app itself, from the existing
 * `bdtpg_get_plugins` and `bdtpg_install_plugin` AJAX actions.
 *
 * When the build is missing, or the `pixel_gallery/admin/react_dashboard`
 * filter returns false, the classic dashboard renders instead.
 *
 * @package PixelGallery
 */

use PixelGallery\Admin\ModuleService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PixelGallery_React_Dashboard {

	const HANDLE  = 'pga-react-admin';
	const PAGE_ID = 'pixel_gallery_options';

	/**
	 * Panel id of the License page.
	 */
	const LICENSE_PANEL = 'pixel_gallery_license_settings';

	/**
	 * On/off settings sections the app manages, in tab order.
	 */
	const SECTIONS = [
		'pixel_gallery_active_modules',
		'pixel_gallery_elementor_extend',
		'pixel_gallery_other_settings',
	];

	/**
	 * Whether the React dashboard renders on this request.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$built = is_readable( BDTPG_PATH . 'build/admin/index.js' )
			&& is_readable( BDTPG_PATH . 'build/admin/index.asset.php' );

		/**
		 * Filters whether the React dashboard replaces the classic one.
		 *
		 * @param bool $enabled Whether the built app is available.
		 */
		return (bool) apply_filters( 'pixel_gallery/admin/react_dashboard', $built );
	}

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
	}

	/**
	 * Whether the current admin screen is the Pixel Gallery dashboard.
	 *
	 * @param string $hook_suffix Admin page hook suffix.
	 * @return bool
	 */
	private function is_dashboard_screen( $hook_suffix = '' ) {
		if ( 'toplevel_page_' . self::PAGE_ID === $hook_suffix ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only picks which assets load.
		return is_admin() && isset( $_GET['page'] ) && self::PAGE_ID === sanitize_key( wp_unslash( $_GET['page'] ) );
	}

	/**
	 * `admin_body_class`: full-bleed layout hooks for the dashboard page.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		if ( $this->is_dashboard_screen() && self::is_enabled() ) {
			$classes .= ' pga-react-admin';
		}

		return $classes;
	}

	/**
	 * `admin_enqueue_scripts`: the app bundle and its bootstrap data.
	 *
	 * @param string $hook_suffix Admin page hook suffix.
	 * @return void
	 */
	public function enqueue( $hook_suffix ) {
		if ( ! $this->is_dashboard_screen( $hook_suffix ) || ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$asset = include BDTPG_PATH . 'build/admin/index.asset.php';
		$deps  = isset( $asset['dependencies'] ) ? (array) $asset['dependencies'] : [];
		$ver   = isset( $asset['version'] ) ? $asset['version'] : BDTPG_VER;

		wp_enqueue_script( self::HANDLE, BDTPG_URL . 'build/admin/index.js', $deps, $ver, true );

		if ( is_readable( BDTPG_PATH . 'build/admin/index.css' ) ) {
			wp_enqueue_style( self::HANDLE, BDTPG_URL . 'build/admin/index.css', [], $ver );
			wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
			wp_style_add_data( self::HANDLE, 'suffix', '' );
		}

		wp_set_script_translations( self::HANDLE, 'pixel-gallery', BDTPG_PATH . 'languages' );

		wp_localize_script( self::HANDLE, 'PixelGalleryAdmin', $this->get_config() );
	}

	/**
	 * Render the dashboard page: the app's mount node, and the PHP-rendered
	 * panels the app moves into its own routes.
	 *
	 * @return void
	 */
	public function render() {
		?>
		<div class="wrap pga-react-wrap">
			<h1 class="screen-reader-text"><?php echo esc_html( BDTPG_TITLE ); ?></h1>
			<div id="pga-admin-app"></div>

			<div id="pga-admin-panels" hidden>
				<?php
				$react_pages = $this->get_react_pages();
				$add_on      = false;
				$license     = false;

				foreach ( $this->get_panels() as $panel ) {
					if ( isset( $react_pages[ $panel['id'] ] ) ) {
						continue; // Rendered by the app.
					}
					if ( self::LICENSE_PANEL === $panel['id'] ) {
						$license = true;
					} else {
						$add_on = true;
					}
				}

				// Add-ons print all their panels in one go, so this runs once when
				// any of them still needs its PHP markup.
				if ( $add_on ) {
					echo '<div class="pga-admin-panel pixel-gallery-dashboard">';
					/** This action is documented in admin/admin-settings.php */
					do_action( 'pixel_gallery/admin/settings_pages' );
					echo '</div>';
				}

				if ( $license ) :
					?>
					<div class="pga-admin-panel pixel-gallery-dashboard" data-pga-panel="<?php echo esc_attr( self::LICENSE_PANEL ); ?>">
						<?php $this->render_license_panel(); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Cached result of get_react_pages().
	 *
	 * @var array|null
	 */
	private $react_pages = null;

	/**
	 * Add-on pages the app renders itself, keyed by panel id.
	 *
	 * An add-on that registers a dashboard tab can hand the app the data for a
	 * native page instead of PHP markup: `[ panel id => data ]`. The app then
	 * renders that page (Pixel Gallery Pro: License, Extra Options, Rollback) and the PHP panel is not rendered. Panels without
	 * data keep their PHP markup, so older add-on versions work unchanged.
	 *
	 * @return array<string, array>
	 */
	private function get_react_pages() {
		if ( null === $this->react_pages ) {
			/**
			 * Filters the add-on pages the dashboard app renders natively.
			 *
			 * @param array<string, array> $pages Panel id => page data.
			 */
			$pages             = (array) apply_filters( 'pixel_gallery/admin/react_pages', [] );
			$this->react_pages = array_filter( $pages, 'is_array' );
		}

		return $this->react_pages;
	}

	/**
	 * Whether Pixel Gallery Pro is active.
	 *
	 * @return bool
	 */
	private static function is_pro() {
		return function_exists( '_is_pg_pro_activated' ) && true === _is_pg_pro_activated();
	}

	/**
	 * Panels add-ons render in PHP: id, title and icon (a dashicons class).
	 *
	 * Add-ons register their tabs on `pixel_gallery/admin/settings_sections`
	 * and print every panel on `pixel_gallery/admin/settings_pages`, each as
	 * `#<id>_page`. License is added last, as the classic tab list does.
	 *
	 * @return array[]
	 */
	private function get_panels() {
		$panels = [];

		/** This filter is documented in admin/class-settings-api.php */
		foreach ( (array) apply_filters( 'pixel_gallery/admin/settings_sections', [] ) as $tab ) {
			if ( empty( $tab['id'] ) || empty( $tab['title'] ) ) {
				continue;
			}

			$panels[] = [
				'id'    => sanitize_key( $tab['id'] ),
				'title' => html_entity_decode( wp_strip_all_tags( (string) $tab['title'] ), ENT_QUOTES, 'UTF-8' ),
				'icon'  => isset( $tab['icon'] ) ? (string) $tab['icon'] : 'dashicons dashicons-screenoptions',
			];
		}

		/** This filter is documented in admin/class-settings-api.php */
		if ( self::is_pro() && apply_filters( 'pixel_gallery/admin/show_license_section', true ) ) {
			$panels[] = [
				'id'    => self::LICENSE_PANEL,
				'title' => __( 'License', 'pixel-gallery' ),
				'icon'  => 'dashicons dashicons-admin-network',
			];
		}

		return $panels;
	}

	/**
	 * Print the License panel through the hook Pixel Gallery Pro listens on.
	 *
	 * @return void
	 */
	private function render_license_panel() {
		if ( has_filter( 'pixel_gallery_license_page' ) ) {
			apply_filters( 'pixel_gallery_license_page', '' );
		} else {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Deprecated alias; renaming would break released Pixel Gallery Pro versions.
			apply_filters( 'pg_license_page', '' );
		}
	}

	/**
	 * Bootstrap data for the app (window.PixelGalleryAdmin).
	 *
	 * @return array
	 */
	private function get_config() {
		$user = wp_get_current_user();

		$react_pages = $this->get_react_pages();
		$panels      = array_map(
			function ( $panel ) use ( $react_pages ) {
				$item = [
					'id'    => $panel['id'],
					'title' => $panel['title'],
					'icon'  => $panel['icon'],
				];

				if ( isset( $react_pages[ $panel['id'] ] ) ) {
					$item['data'] = $react_pages[ $panel['id'] ];
				}

				return $item;
			},
			$this->get_panels()
		);

		return [
			'version'            => BDTPG_VER,
			'title'              => BDTPG_TITLE,
			'whiteLabel'         => 'Pixel Gallery' !== BDTPG_TITLE,
			'isPro'              => self::is_pro(),
			'userName'           => $user ? $user->display_name : '',
			'adminUrl'           => admin_url(),
			'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
			'saveNonce'          => wp_create_nonce( 'pixel-gallery-settings-save-nonce' ),
			'logoUrl'            => BDTPG_ASSETS_URL . 'images/logo-with-text.svg',
			'logoIconUrl'        => BDTPG_ASSETS_URL . 'images/logo.svg',
			'newPageUrl'         => admin_url( 'post-new.php?post_type=page' ),
			'setupWizardUrl'     => admin_url( '?pg_setup_wizard=show' ),
			'docsUrl'            => 'https://bdthemes.com/all-knowledge-base-of-pixel-gallery/',
			'supportUrl'         => 'https://bdthemes.com/support/',
			'videosUrl'          => 'https://www.youtube.com/playlist?list=PLP0S85GEw7DPv5T-Ara11Zvplmk4ty0jy',
			'demoUrl'            => 'https://pixelgallery.pro/',
			'featureUrl'         => 'https://feedback.bdthemes.com/b/6vr2250l/feature-requests/idea/new',
			'reviewUrl'          => 'https://wordpress.org/support/plugin/pixel-gallery/reviews/',
			'whatsNewUrl'        => 'https://wordpress.org/plugins/pixel-gallery/#developers',
			'pricingUrl'         => 'https://pixelgallery.pro/pricing/',
			'getProUrl'          => 'https://store.bdthemes.com/pixel-gallery?utm_source=PixelGallery&utm_medium=PluginPage&utm_campaign=PixelGallery&coupon=FREETOPRO',
			'sections'           => $this->get_sections(),
			'system'             => $this->get_system_checks(),
			'releases'           => $this->get_releases(),
			'panels'             => $panels,
			'assets'             => $this->get_asset_stats(),
			'plugins'            => [
				'nonce'        => wp_create_nonce( 'bdtpg_get_plugins_nonce' ),
				'installNonce' => wp_create_nonce( 'bdtpg_install_plugin_nonce' ),
				'canInstall'   => current_user_can( 'install_plugins' ),
				'canActivate'  => current_user_can( 'activate_plugins' ),
			],
			'environment'        => [
				'wp_version'  => get_bloginfo( 'version' ),
				'php_version' => phpversion(),
				'server'      => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
				'language'    => get_locale(),
			],
		];
	}

	/**
	 * Plain text from a label or description that may hold HTML or entities.
	 *
	 * @param mixed $text Raw text.
	 * @return string
	 */
	private static function plain( $text ) {
		return trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' ) ) );
	}

	/**
	 * Every settings section with its fields and current values.
	 *
	 * @return array
	 */
	private function get_sections() {
		$fields_by_section = ModuleService::get_widget_settings(
			function ( $settings ) {
				return $settings['settings_fields'];
			}
		);

		$is_pro    = self::is_pro();
		$used      = class_exists( 'PixelGallery_Admin_Settings' ) ? (array) PixelGallery_Admin_Settings::get_used_widgets() : [];
		$has_usage = class_exists( '\Elementor\Modules\Usage\Module' );
		$sections  = [];

		foreach ( self::SECTIONS as $section_id ) {
			$fields = isset( $fields_by_section[ $section_id ] ) ? (array) $fields_by_section[ $section_id ] : [];
			$stored = get_option( $section_id, [] );
			$stored = is_array( $stored ) ? $stored : [];
			$items  = [];
			$group  = null;

			foreach ( $fields as $field ) {
				$type = isset( $field['type'] ) ? $field['type'] : '';

				if ( 'start_group' === $type ) {
					$group = [
						'title' => isset( $field['label'] ) ? self::plain( $field['label'] ) : '',
						'desc'  => isset( $field['desc'] ) ? wp_kses_post( (string) $field['desc'] ) : '',
					];
					continue;
				}

				if ( 'end_group' === $type ) {
					$group = null;
					continue;
				}

				if ( 'checkbox' !== $type || empty( $field['name'] ) ) {
					continue;
				}

				$name    = (string) $field['name'];
				$default = isset( $field['default'] ) ? (string) $field['default'] : 'off';
				$value   = isset( $stored[ $name ] ) ? (string) $stored[ $name ] : $default;
				$slug    = 'pg-' . str_replace( '_', '-', $name );
				$is_paid = isset( $field['widget_type'] ) && 'pro' === $field['widget_type'];

				$item = [
					'name'         => $name,
					'label'        => isset( $field['label'] ) ? self::plain( $field['label'] ) : $name,
					'enabled'      => 'on' === $value,
					'default'      => $default,
					'widget_type'  => $is_paid ? 'pro' : 'free',
					'content_type' => isset( $field['content_type'] ) ? (string) $field['content_type'] : '',
					'demo_url'     => ! empty( $field['demo_url'] ) ? esc_url_raw( $field['demo_url'] ) : '',
					'video_url'    => ! empty( $field['video_url'] ) ? esc_url_raw( $field['video_url'] ) : '',
					'usage_count'  => isset( $used[ $slug ] ) ? (int) $used[ $slug ] : 0,
					'has_usage'    => $has_usage,
					'is_locked'    => $is_paid && ! $is_pro,
				];

				if ( ! empty( $field['tooltip'] ) ) {
					$item['tooltip']     = self::plain( $field['tooltip'] );
					$item['description'] = $item['tooltip'];
				}

				if ( $group ) {
					$item['group']       = $group;
					$item['description'] = self::plain( $group['desc'] );
				}

				$items[] = $item;
			}

			$sections[ $section_id ] = [
				'id'     => $section_id,
				'fields' => $items,
				// Every stored value, so a save never drops fields the app does not list.
				'stored' => array_map( 'strval', array_filter( $stored, 'is_scalar' ) ),
			];
		}

		return $sections;
	}

	/**
	 * Asset Optimization state and the combined files it has written.
	 *
	 * @return array
	 */
	private function get_asset_stats() {
		$stats = class_exists( '\PixelGallery\Includes\Asset_Combiner' )
			? \PixelGallery\Includes\Asset_Combiner::stats()
			: [ 'css' => 0, 'js' => 0, 'bytes' => 0, 'version' => '' ];

		return [
			'enabled' => function_exists( 'pixel_gallery_is_asset_combining_enabled' ) && pixel_gallery_is_asset_combining_enabled(),
			'css'     => (int) $stats['css'],
			'js'      => (int) $stats['js'],
			'size'    => size_format( (int) $stats['bytes'] ),
			'version' => isset( $stats['version'] ) ? (string) $stats['version'] : '',
		];
	}

	/**
	 * Server environment checks for the System Status page.
	 *
	 * @return array[]
	 */
	private function get_system_checks() {
		$php_version   = phpversion();
		$max_execution = (int) ini_get( 'max_execution_time' );
		$memory_limit  = (string) ini_get( 'memory_limit' );
		$post_limit    = (string) ini_get( 'post_max_size' );
		$uploads       = wp_upload_dir( null, false );
		$writable      = empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] );
		$debug         = defined( 'WP_DEBUG' ) && WP_DEBUG;

		return [
			[
				'id'    => 'php',
				'label' => __( 'PHP version', 'pixel-gallery' ),
				'value' => $php_version,
				'pass'  => version_compare( $php_version, '7.4.0', '>=' ),
				'hint'  => __( 'Min: 7.4', 'pixel-gallery' ),
			],
			[
				'id'    => 'max_execution_time',
				'label' => __( 'Max execution time', 'pixel-gallery' ),
				'value' => (string) $max_execution,
				'pass'  => 0 === $max_execution || $max_execution >= 90,
				'hint'  => __( 'Min: 90 recommended', 'pixel-gallery' ),
			],
			[
				'id'    => 'memory_limit',
				'label' => __( 'Memory limit', 'pixel-gallery' ),
				'value' => $memory_limit,
				'pass'  => '-1' === $memory_limit || wp_convert_hr_to_bytes( $memory_limit ) >= 512 * MB_IN_BYTES,
				'hint'  => __( 'Min: 512M recommended', 'pixel-gallery' ),
			],
			[
				'id'    => 'post_max_size',
				'label' => __( 'Max post size', 'pixel-gallery' ),
				'value' => $post_limit,
				'pass'  => wp_convert_hr_to_bytes( $post_limit ) >= 32 * MB_IN_BYTES,
				'hint'  => __( 'Min: 32M recommended', 'pixel-gallery' ),
			],
			[
				'id'    => 'uploads_writable',
				'label' => __( 'Uploads folder writable', 'pixel-gallery' ),
				'value' => $writable ? __( 'Yes', 'pixel-gallery' ) : __( 'No', 'pixel-gallery' ),
				'pass'  => $writable,
			],
			[
				'id'    => 'gzip',
				'label' => __( 'GZip enabled', 'pixel-gallery' ),
				'value' => function_exists( 'gzencode' ) ? __( 'Yes', 'pixel-gallery' ) : __( 'No', 'pixel-gallery' ),
				'pass'  => function_exists( 'gzencode' ),
			],
			[
				'id'    => 'multisite',
				'label' => __( 'Multisite', 'pixel-gallery' ),
				'value' => is_multisite() ? __( 'Multisite', 'pixel-gallery' ) : __( 'Single site', 'pixel-gallery' ),
				'pass'  => true,
			],
			[
				'id'    => 'debug',
				'label' => __( 'Debug mode', 'pixel-gallery' ),
				'value' => $debug ? __( 'On', 'pixel-gallery' ) : __( 'Off', 'pixel-gallery' ),
				'pass'  => ! $debug,
			],
		];
	}

	/**
	 * The latest releases from readme.txt, for the Recent activity card.
	 *
	 * @param int $limit Number of releases.
	 * @return array[]
	 */
	private function get_releases( $limit = 5 ) {
		$readme = BDTPG_PATH . 'readme.txt';

		if ( ! is_readable( $readme ) ) {
			return [];
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin file.
		$text = (string) file_get_contents( $readme );
		$at   = strpos( $text, '== Changelog ==' );

		if ( false === $at ) {
			return [];
		}

		$text     = substr( $text, $at );
		$releases = [];

		if ( ! preg_match_all( '/^=\s*([0-9][0-9.]*)\s*(?:\[([^\]]*)\])?\s*=\s*$/m', $text, $heads, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
			return [];
		}

		$map = [
			'added'      => 'added',
			'new'        => 'added',
			'fixed'      => 'fixed',
			'fix'        => 'fixed',
			'security'   => 'fixed',
			'improved'   => 'updated',
			'updated'    => 'updated',
			'changed'    => 'updated',
			'compliance' => 'updated',
			'removed'    => 'removed',
		];

		foreach ( $heads as $index => $head ) {
			if ( count( $releases ) >= $limit ) {
				break;
			}

			$start = $head[0][1] + strlen( $head[0][0] );
			$end   = isset( $heads[ $index + 1 ] ) ? $heads[ $index + 1 ][0][1] : strlen( $text );
			$body  = substr( $text, $start, $end - $start );

			preg_match_all( '/^\*\s*(?:([A-Za-z]+):)?\s*(.+)$/m', $body, $lines, PREG_SET_ORDER );

			$sections   = [];
			$highlights = [];

			foreach ( $lines as $line ) {
				$kind = strtolower( trim( (string) $line[1] ) );
				$key  = isset( $map[ $kind ] ) ? $map[ $kind ] : 'updated';

				$sections[ $key ] = isset( $sections[ $key ] ) ? $sections[ $key ] + 1 : 1;
				$highlights[]     = wp_strip_all_tags( trim( (string) $line[2] ) );
			}

			$releases[] = [
				'version'    => $head[1][0],
				'date'       => isset( $head[2][0] ) ? trim( (string) $head[2][0] ) : '',
				'is_current' => BDTPG_VER === $head[1][0],
				'sections'   => $sections,
				'highlights' => array_slice( $highlights, 0, 2 ),
			];
		}

		return $releases;
	}
}
