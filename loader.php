<?php

namespace PixelGallery;

use Elementor\Plugin;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Main class for element pack
 */
class Pixel_Gallery_Loader {

    /**
     * @var Pixel_Gallery_Loader
     */
    private static $_instance;

    /**
	 * @var Manager
	 */
	private $_modules_manager;

    public $elements_data = [
        'sections' => [],
        'columns'  => [],
        'widgets'  => [],
    ];

    private function get_upload_dir() {
        return trailingslashit(wp_upload_dir()['basedir']) . 'pixel-gallery/minified/';
    }

    private function get_upload_url() {
        return trailingslashit(wp_upload_dir()['baseurl']) . 'pixel-gallery/minified/';
    }

    /**
     * @return string
     * @deprecated
     *
     */
    public function get_version() {
        return BDTPG_VER;
    }

    /**
     * return active theme
     */
    public function get_theme() {
        return wp_get_theme();
    }

    /**
     * Throw error on object clone
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @return void
     * @since 1.0.0
     */
    public function __clone() {
        // Cloning instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'pixel-gallery'), '1.6.0');
    }

    /**
     * Disable unserializing of the class
     *
     * @return void
     * @since 1.0.0
     */
    public function __wakeup() {
        // Unserializing instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'pixel-gallery'), '1.6.0');
    }

    /**
     * @return Plugin
     */

    public static function elementor() {
        return Plugin::$instance;
    }

    /**
     * @return Pixel_Gallery_Loader
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        do_action('bdthemes_pixel_gallery/init');
        return self::$_instance;
    }


    /**
     * we loaded module manager + admin php from here
     * @return [type] [description]
     */
    private function _includes() {

        $essential_shortcodes = pixel_gallery_option('essential-shortcodes', 'pixel_gallery_other_settings', 'off');

        // Admin settings controller
        require_once BDTPG_ADMIN_PATH . 'module-settings.php';
        //Assets Manager
        require_once 'admin/optimizer/asset-minifier-manager.php';

        // Dynamic Select control
        require_once BDTPG_INC_PATH . 'controls/select-input/dynamic-select-input-module.php';
        require_once BDTPG_INC_PATH . 'controls/select-input/dynamic-select.php';

        // Global Controls
        require_once BDTPG_PATH . 'traits/global-widget-controls.php';
        require_once BDTPG_PATH . 'traits/global-terms-query-controls.php';
        // require_once BDTPG_PATH . 'traits/global-mask-controls.php';


        // All modules loading from here
        require_once BDTPG_INC_PATH . 'modules-manager.php';


        // Shortcode loader for works some essential shortcode that need for any purpose
        if ($essential_shortcodes == 'on') {
            require_once BDTPG_INC_PATH . 'shortcodes/shortcode-loader.php';
        }
    }

    /**
     * Autoloader function for all classes files
     *
     * @param  [type] class [description]
     *
     * @return [type]        [description]
     */
    public function autoload($class) {
        if (0 !== strpos($class, __NAMESPACE__)) {
            return;
        }


        $class_to_load = $class;

        if (!class_exists($class_to_load)) {
            $filename = strtolower(
                preg_replace(
                    ['/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z0-9])/', '/_/', '/\\\/'],
                    ['', '$1-$2', '-', DIRECTORY_SEPARATOR],
                    $class_to_load
                )
            );

            $filename = BDTPG_PATH . $filename . '.php';

            if (is_readable($filename)) {
                include($filename);
            }
        }
    }

    /**
     * Register all script that need for any specific widget on call basis.
     * @return [type] [description]
     */
    public function register_site_scripts() {
        wp_register_script('pg-animations', BDTPG_ASSETS_URL . 'js/extensions/pg-animations.min.js', ['jquery'], '', true);
    }

    /**
     * Loading site related style from here.
     * @return [type] [description]
     */
    public function enqueue_site_styles() {

        $direction_suffix = is_rtl() ? '.rtl' : '';

        wp_enqueue_style('pg-helper', BDTPG_ASSETS_URL . 'css/pg-helper' . $direction_suffix . '.css', ['e-swiper'], BDTPG_VER);
        wp_enqueue_style('pg-font', BDTPG_ASSETS_URL . 'css/pg-font' . $direction_suffix . '.css', [], BDTPG_VER);
    }

    public function enqueue_editor_scripts() {

        wp_enqueue_script(
            'pg-editor',
            BDTPG_ASSETS_URL . 'js/pg-editor.min.js',
            [
                'backbone-marionette',
                'elementor-common-modules',
                'elementor-editor-modules',
            ],
            BDTPG_VER,
            true
        );

        $_is_pg_pro_activated = false;
        if (function_exists('pg_license_validation') && true === pg_license_validation()) {
            $_is_pg_pro_activated = true;
        }

        $localize_data = [
            'pro_installed'  => _is_pg_pro_activated(),
            'pro_license_activated'  => $_is_pg_pro_activated,
            'promotional_widgets'   => [],
        ];

        if (!$_is_pg_pro_activated) {
            $pro_widget_map = new \PixelGallery\Includes\Pro_Widget_Map();
            $localize_data['promotional_widgets'] = $pro_widget_map->get_pro_widget_map();
        }

        wp_localize_script('pg-editor', 'PixelGalleryConfigEditor', $localize_data);
    }

    public function enqueue_editor_styles() {
        $direction_suffix = is_rtl() ? '.rtl' : '';

        wp_enqueue_style('pg-editor', BDTPG_ASSETS_URL . 'css/pg-editor' . $direction_suffix . '.css', '', BDTPG_VER);
    }


    public function enqueue_minified_css() {
        $direction_suffix = is_rtl() ? '.rtl' : '';

        $upload_dir = $this->get_upload_dir() . 'css/pg-styles.css';
        $version    = get_option('pixel-gallery-minified-asset-manager-version');

        if (pixel_gallery_is_asset_optimization_enabled() && file_exists($upload_dir)) {
            $upload_url = $this->get_upload_url() . 'css/pg-styles.css';
            wp_register_style('pg-styles', $upload_url, [], $version);
        } else {
            wp_register_style('pg-styles', BDTPG_URL . 'assets/css/pg-styles' . $direction_suffix . '.css', [], BDTPG_VER);
        }

        if (pixel_gallery_is_asset_optimization_enabled()) {
            wp_enqueue_style('pg-styles');
        }
    }

    public function enqueue_minified_js() {

        $upload_dir = $this->get_upload_dir() . 'js/pg-scripts.js';
        $version    = get_option('pixel-gallery-minified-asset-manager-version');

        if (pixel_gallery_is_asset_optimization_enabled() && file_exists($upload_dir)) {
            $upload_url = $this->get_upload_url() . 'js/pg-scripts.min.js';

            wp_register_script('pg-scripts', $upload_url, ['elementor-frontend'], $version, true);
        } else {
            wp_register_script('pg-scripts', BDTPG_URL . 'assets/js/pg-scripts.min.js', ['elementor-frontend'], BDTPG_VER, true);
        }

        if (pixel_gallery_is_asset_optimization_enabled()) {
            wp_enqueue_script('pg-scripts');
        }
    }


    /**
     * Callback to shortcodes template
     * @param array $atts attributes for shortcode.
     */
    public function shortcode_template($atts) {

        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts,
            'rooten_custom_template'
        );

        $id = !empty($atts['id']) ? intval($atts['id']) : '';

        if (empty($id)) {
            return '';
        }

        return self::elementor()->frontend->get_builder_content_for_display($id);
    }


    /**
     * Add pixel_gallery_ajax_login() function with wp_ajax_nopriv_ function
     * @return [type] [description]
     */
    public function pixel_gallery_ajax_login_init() {
        // Enable the user with no privileges to run pixel_gallery_ajax_login() in AJAX
        add_action('wp_ajax_nopriv_pixel_gallery_ajax_login', [$this, "pixel_gallery_ajax_login"]);
    }

    /**
     * For ajax login
     * @return [type] [description]
     */
    public function pixel_gallery_ajax_login() {
        // First check the nonce, if it fails the function will break
        check_ajax_referer('ajax-login-nonce', 'bdt-user-login-sc');

        // Nonce is checked, get the POST data and sign user on
        $access_info                  = [];
        $access_info['user_login']    = !empty($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : "";
        $access_info['user_password'] = !empty($_POST['user_password']) ? sanitize_text_field($_POST['user_password']) : "";
        $access_info['remember']      = !empty($_POST['rememberme']) ? true : false;
        $user_signon                  = wp_signon($access_info, false);

        if (!is_wp_error($user_signon)) {
            echo wp_json_encode(
                [
                    'loggedin' => true,
                    'message'  => esc_html_x('Login successful, Redirecting...', 'User Login and Register', 'pixel-gallery')
                ]
            );
        } else {
            echo wp_json_encode(
                [
                    'loggedin' => false,
                    'message'  => esc_html_x('Oops! Wrong username or password!', 'User Login and Register', 'pixel-gallery')
                ]
            );
        }

        die();
    }


    /**
     * initialize the category
     * @return void
     */
    public function pixel_gallery_init() {

        $this->_modules_manager = new Manager();

        do_action('pixel_gallery/init');
    }


    /**
     * initialize the category
     * @return [type] [description]
     */
    public function pixel_gallery_category_register() {

        $elementor = Plugin::$instance;

        // Add element category in panel
        $elementor->elements_manager->add_category(BDTPG_SLUG, ['title' => BDTPG_TITLE, 'icon' => 'font']);
    }

    private function setup_hooks() {


        add_action('elementor/elements/categories_registered', [$this, 'pixel_gallery_category_register']);
        add_action('elementor/init', [$this, 'pixel_gallery_init']);


        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_styles']);

        add_action('elementor/frontend/before_register_scripts', [$this, 'register_site_scripts']);

        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);

        add_action('elementor/frontend/after_register_styles', [$this, 'enqueue_site_styles']);

        // For frontend css load
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_minified_css']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_minified_js']);


        add_shortcode('rooten_custom_template', [$this, 'shortcode_template']);


        // When user not login add this action
        if (!is_user_logged_in()) {
            add_action('elementor/init', [$this, 'pixel_gallery_ajax_login_init']);
        }
    }

    public function init(){
		if ( ! defined( 'BDTPG_CH' ) && is_admin() ) {
			require_once BDTPG_ADMIN_PATH . 'admin.php';
			new Admin();
		}
    }

    /**
     * Pixel_Gallery_Loader constructor.
     */
    private function __construct() {
        // Register class automatically
        spl_autoload_register([$this, 'autoload']);
        // Include some backend files
        $this->_includes();

        // Finally hooked up all things here
        $this->setup_hooks();

        add_action('init', [$this, 'init']);
    }
}

if (!defined('BDTPG_TESTS')) {
    // In tests we run the instance manually.
    Pixel_Gallery_Loader::instance();
}
// handy function for push data
function pixel_gallery_config() {
    return Pixel_Gallery_Loader::instance();
}
