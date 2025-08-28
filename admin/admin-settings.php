<?php

use PixelGallery\Notices;
use PixelGallery\Utils;
use PixelGallery\Admin\ModuleService;
use Elementor\Modules\Usage\Module;
use Elementor\Tracker;

/**
 * Pixel Gallery Admin Settings Class
 */

class PixelGallery_Admin_Settings {

    public static $modules_list  = null;
    public static $modules_names = null;

    public static $modules_list_only_widgets  = null;
    public static $modules_names_only_widgets = null;


    const PAGE_ID = 'pixel_gallery_options';

    private $settings_api;

    function __construct() {
        $this->settings_api = new PixelGallery_Settings_API;

        if (!defined('BDTPG_HIDE')) {
            add_action('admin_init', [$this, 'admin_init']);
            add_action('admin_menu', [$this, 'admin_menu'], 201);
        }

        if (!Tracker::is_allow_track()) {
            add_action('admin_notices', [$this, 'allow_tracker_activate_notice'], 10, 3);
        }
    }

    /**
     * Get used widgets.
     *
     * @access public
     * @return array
     * @since 6.0.0
     *
     */
    public static function get_used_widgets() {

        $used_widgets = array();

        if (class_exists('Elementor\Modules\Usage\Module')) {
            $module     = Module::instance();
 			
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

    public static function get_used_only_widgets() {

        $used_widgets = array();

        if (class_exists('Elementor\Modules\Usage\Module')) {
            $module     = Module::instance();
            
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

    public static function get_unused_widgets() {

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

    public static function get_unused_only_widgets() {

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

    public static function get_pg_widgets_names() {
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

    public static function get_pg_only_widgets() {
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

    public static function get_url() {
        return admin_url('admin.php?page=' . self::PAGE_ID);
    }

    /**
     * Init settings API
     *
     * @access public
     *
     */

    public function admin_init() {

        //set the settings
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->pixel_gallery_admin_settings());

        //initialize settings
        $this->settings_api->admin_init();
        $this->pg_redirect_to_get_pro();

        if ( _is_pg_pro_activated() ) {
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
    public function pg_redirect_to_get_pro() {
        if (isset($_GET['page']) && $_GET['page'] === self::PAGE_ID . '_get_pro') {
            wp_redirect('https://pixelgallery.pro/pricing/?utm_source=PixelGallery&utm_medium=PluginPage&utm_campaign=30%OffOnPixelGallery&coupon=FREETOPRO');
            exit;
        }
    }

     /**
     * Redirect to license renewal page
     *
     * @access public
     *
     */
    public function bdt_redirect_to_renew_link() {
        if (isset($_GET['page']) && $_GET['page'] === self::PAGE_ID . '_license_renew') {
            wp_redirect('https://account.bdthemes.com/');
            exit;
        }
    }

    public function admin_menu() {
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
            BDTPG_TITLE,
            esc_html__('Core Widgets', 'pixel-gallery'),
            'manage_options',
            self::PAGE_ID . '#pixel_gallery_active_modules',
            [$this, 'display_page']
        );

        add_submenu_page(
            self::PAGE_ID,
            BDTPG_TITLE,
            esc_html__('Extensions', 'pixel-gallery'),
            'manage_options',
            self::PAGE_ID . '#pixel_gallery_elementor_extend',
            [$this, 'display_page']
        );

        if (!defined('BDTPG_LO')) {
            add_submenu_page(
                self::PAGE_ID,
                BDTPG_TITLE,
                esc_html__('Other Settings', 'pixel-gallery'),
                'manage_options',
                self::PAGE_ID . '#pixel_gallery_other_settings',
                [$this, 'display_page']
            );
        }

        if (true !== _is_pg_pro_activated()) {
            add_submenu_page(
                self::PAGE_ID,
                BDTPG_TITLE,
                esc_html__('Upgrade For 30% Off!', 'pixel-gallery'),
                'manage_options',
                self::PAGE_ID . '_get_pro',
                [$this, 'display_page']
            );
        }
    }

    /**
     * Get SVG Icons of Pixel Gallery
     *
     * @access public
     * @return string
     */

    public function pixel_gallery_icon() {
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNS4zLjEsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCA1MDIuMiA1MDEuOCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTAyLjIgNTAxLjg7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxyZWN0IHg9Ijg4LjkiIHk9Ijk5IiBjbGFzcz0ic3QwIiB3aWR0aD0iMzQuMSIgaGVpZ2h0PSIzNC4xIi8+DQoJPHJlY3QgeD0iNTQuMiIgeT0iNTgiIGNsYXNzPSJzdDAiIHdpZHRoPSIyMS43IiBoZWlnaHQ9IjIxLjciLz4NCgk8cmVjdCB4PSI3MS40IiB5PSIyLjQiIGNsYXNzPSJzdDAiIHdpZHRoPSI5LjkiIGhlaWdodD0iOS45Ii8+DQoJPHJlY3QgeD0iOTkuNyIgeT0iMzUuNCIgY2xhc3M9InN0MCIgd2lkdGg9IjE0LjgiIGhlaWdodD0iMTQuOCIvPg0KCTxyZWN0IHg9Ijk4LjciIHk9IjE5NC4zIiBjbGFzcz0ic3QwIiB3aWR0aD0iMTQuOCIgaGVpZ2h0PSIxNC44Ii8+DQoJPHJlY3QgeD0iMTgyLjkiIHk9IjEyLjgiIGNsYXNzPSJzdDAiIHdpZHRoPSIxMi4zIiBoZWlnaHQ9IjEyLjMiLz4NCgk8cmVjdCB4PSIxNDEuMSIgeT0iMTQzLjYiIGNsYXNzPSJzdDAiIHdpZHRoPSI2MC40IiBoZWlnaHQ9IjYwLjQiLz4NCgk8cmVjdCB4PSIxNDMuMiIgeT0iNDYuNiIgY2xhc3M9InN0MCIgd2lkdGg9IjM1LjMiIGhlaWdodD0iMzUuMyIvPg0KCTxyZWN0IHg9IjU5LjciIHk9IjE1MS4xIiBjbGFzcz0ic3QwIiB3aWR0aD0iMjIiIGhlaWdodD0iMjIiLz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzk4LjIsNjIuNGMtMzMtMzIuNS03My40LTQ4LjgtMTIxLjMtNDguOGgtNDMuNnYzMi4yaC0yOS42djcyLjNoNzMuMmMxNy4xLDAsMzEuMyw2LjEsNDIuNiwxOC4yDQoJCWMxMS4xLDEyLjEsMTYuNywyNi45LDE2LjcsNDQuNnMtNS42LDMyLjUtMTYuNyw0NC42Yy0xMS4xLDEyLjEtMjUuMywxOC4yLTQyLjYsMTguMmgtNzMuMmwwLDBoLTYxLjV2NjQuOUg5Mi4zdjE5My4xaDExMS42VjM0OC4zDQoJCWg3My4yYzQ3LjksMCw4OC40LTE2LjMsMTIxLjMtNDguOHM0OS41LTcyLjEsNDkuNS0xMTguNUM0NDcuNywxMzQuNCw0MzEuMiw5NSwzOTguMiw2Mi40eiIvPg0KCTxyZWN0IHg9Ijc2LjIiIHk9IjI0My4zIiBjbGFzcz0ic3QwIiB3aWR0aD0iNDQuNSIgaGVpZ2h0PSI0NC41Ii8+DQo8L2c+DQo8L3N2Zz4NCg==';
    }

    /**
     * Get SVG Icons of Pixel Gallery
     *
     * @access public
     * @return array
     */

    public function get_settings_sections() {
        $sections = [
            [
                'id'    => 'pixel_gallery_active_modules',
                'title' => esc_html__('Core Widgets', 'pixel-gallery')
            ],
            [
                'id'    => 'pixel_gallery_elementor_extend',
                'title' => esc_html__('Extensions', 'pixel-gallery')
            ],
            [
                'id'    => 'pixel_gallery_other_settings',
                'title' => esc_html__('Other Settings', 'pixel-gallery'),
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

    protected function pixel_gallery_admin_settings() {

        return ModuleService::get_widget_settings(function ($settings) {
            $settings_fields    = $settings['settings_fields'];

            self::$modules_list = $settings_fields['pixel_gallery_active_modules'];
            self::$modules_list_only_widgets  = $settings_fields['pixel_gallery_active_modules'];

            return $settings_fields;
        });
    }

    /**
     * Get Welcome Panel
     *
     * @access public
     * @return void
     */

    public function pixel_gallery_welcome() {
?>

        <div class="pg-dashboard-panel" bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

            <div class="bdt-grid bdt-grid-medium" bdt-grid bdt-height-match="target: > div > .bdt-card">
                <div class="bdt-width-1-2@m bdt-width-1-4@l">
                    <div class="pg-widget-status bdt-card bdt-card-body">

                        <?php
                        $used_widgets    = count(self::get_used_widgets());
                        $un_used_widgets = count(self::get_unused_widgets());
                        ?>
                        <div class="pg-count-canvas-wrap">
                            <h1 class="pg-feature-title"><?php echo esc_html__('All Widgets', 'pixel-gallery'); ?></h1>
                            <div class="bdt-flex bdt-flex-between bdt-flex-middle">
                                <div class="pg-count-wrap">
                                    <div class="pg-widget-count"><?php echo esc_html__('Used:', 'pixel-gallery'); ?> <b><?php echo esc_html__($used_widgets, 'pixel-gallery'); ?></b></div>
                                    <div class="pg-widget-count"><?php echo esc_html__('Unused:', 'pixel-gallery'); ?> <b><?php echo esc_html__($un_used_widgets, 'pixel-gallery'); ?></b></div>
                                    <div class="pg-widget-count"><?php echo esc_html__('Total:', 'pixel-gallery'); ?> <b><?php echo esc_html__($used_widgets + $un_used_widgets, 'pixel-gallery'); ?></b>
                                    </div>
                                </div>

                                <div class="pg-canvas-wrap">
                                    <canvas id="bdt-db-total-status" style="height: 100px; width: 100px;" data-label="Total Widgets Status - (<?php echo esc_html__($used_widgets + $un_used_widgets, 'pixel-gallery'); ?>)" data-labels="<?php echo esc_attr('Used, Unused'); ?>" data-value="<?php echo esc_attr($used_widgets) . ',' . esc_attr($un_used_widgets); ?>" data-bg="#FFD166, #fff4d9" data-bg-hover="#0673e1, #e71522"></canvas>
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
                                    <div class="pg-widget-count"><?php esc_html_e('Core: ', 'pixel-gallery'); ?><b id="bdt-total-widgets-status-core"></b></div>
                                    <div class="pg-widget-count"><?php esc_html_e('Total:', 'pixel-gallery'); ?> <b id="bdt-total-widgets-status-heading"></b></div>
                                </div>

                                <div class="pg-canvas-wrap">
                                    <canvas id="bdt-total-widgets-status" style="height: 100px; width: 100px;" data-labels="Total Active, Total Widgets" data-bg="#0680d6, #E6F9FF" data-bg-hover="#0673e1, #b6f9e8">
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
                        <p>Feeling like to consult with an expert? Take live Chat support immediately from <a href="https://pixelgallery.com" target="_blank" rel="">PixelGallery</a>. We are always
                            ready to help
                            you 24/7.</p>
                        <p><strong>Or if you’re facing technical issues with our plugin, then please create a support
                                ticket</strong></p>
                        <a class="bdt-button bdt-btn-blue bdt-margin-small-top bdt-margin-small-right" target="_blank" rel="" href="https://bdthemes.com/all-knowledge-base-of-pixel-gallery/">Knowledge
                            Base</a>
                        <a class="bdt-button bdt-btn-grey bdt-margin-small-top" target="_blank" href="https://bdthemes.com/support/">Get Support</a>
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
                        <a class="bdt-button bdt-btn-yellow bdt-margin-small-top" target="_blank" rel="" href="https://feedback.bdthemes.com/b/6vr2250l/feature-requests/">Request Feature</a>
                    </div>
                </div>

                <div class="bdt-width-1-2@m">
                    <div class="bdt-card bdt-card-body pg-tryaddon-bg">
                        <h1 class="pg-feature-title">Try Our Others Addons</h1>
                        <p style="max-width: 520px;">
                            <b>Element Pack, Prime Slider, Ultimate Post Kit & Ultimate Store Kit</b> addons for <b>Elementor</b> is the best slider &
                            blogs plugin for WordPress.
                        </p>
                        <div class="bdt-others-plugins-link">
                            <a class="bdt-button bdt-btn-ep bdt-margin-small-right" target="_blank" href="https://wordpress.org/plugins/bdthemes-element-pack-lite/" bdt-tooltip="Element Pack Lite provides more than 50+ essential elements for everyday applications to simplify the whole web building process. It's Free! Download it.">Element pack</a>
                            <a class="bdt-button bdt-btn-ps bdt-margin-small-right" target="_blank" href="https://wordpress.org/plugins/bdthemes-prime-slider-lite/" bdt-tooltip="The revolutionary slider builder addon for Elementor with next-gen superb interface. It's Free! Download it.">Prime Slider</a>
                            <a class="bdt-button bdt-btn-upk bdt-margin-small-right" target="_blank" rel="" href="https://wordpress.org/plugins/ultimate-post-kit/" bdt-tooltip="Best blogging addon for building quality blogging website with fine-tuned features and widgets. It's Free! Download it.">Ultimate Post Kit</a>
                            <a class="bdt-button bdt-btn-usk bdt-margin-small-right" target="_blank" rel="" href="https://wordpress.org/plugins/ultimate-store-kit/" bdt-tooltip="The only eCommmerce addon for answering all your online store design problems in one package. It's Free! Download it.">Ultimate Store Kit</a>
                            <a class="bdt-button bdt-btn-live-copy bdt-margin-small-right" target="_blank" rel="" href="https://wordpress.org/plugins/live-copy-paste/" bdt-tooltip="Superfast cross-domain copy-paste mechanism for WordPress websites with true UI copy experience. It's Free! Download it.">Live Copy Paste</a>
                        </div>

                    </div>
                </div>
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

    function pixel_gallery_get_pro() {
    ?>
        <div class="pg-dashboard-panel" bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">

            <div class="bdt-grid" bdt-grid bdt-height-match="target: > div > .bdt-card" style="max-width: 800px; margin-left: auto; margin-right: auto;">
                <div class="bdt-width-1-1@m pg-comparision bdt-text-center">
                    <div class="bdt-flex bdt-flex-between bdt-flex-middle">
                        <div class="bdt-text-left">
                            <h1 class="bdt-text-bold">WHY GO WITH PRO?</h1>
                            <h2>Just Compare With Pixel Gallery Free Vs Pro</h2>
                        </div>
                        <?php if (true !== _is_pg_pro_activated()) : ?>
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
                                    <div class="bdt-width-expand@m"><span bdt-tooltip="pos: top-left; title: Lite have 35+ Widgets but Pro have 100+ core widgets">Core Widgets</span></div>
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

                            <?php if (true !== _is_pg_pro_activated()) : ?>
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
     * Display System Requirement
     *
     * @access public
     * @return void
     */

    function pixel_gallery_system_requirement() {
        $php_version        = phpversion();
        $max_execution_time = ini_get('max_execution_time');
        $memory_limit       = ini_get('memory_limit');
        $post_limit         = ini_get('post_max_size');
        $uploads            = wp_upload_dir();
        $upload_path        = $uploads['basedir'];
        $yes_icon           = '<i class="dashicons-before dashicons-yes"></i>';
        $no_icon            = '<i class="dashicons-before dashicons-no-alt"></i>';
        $icon_validation = [
            'i' => [
                'class' => []
            ]
        ];

        $environment = Utils::get_environment_info();

    ?>
        <ul class="check-system-status bdt-grid bdt-child-width-1-2@m bdt-grid-small ">
            <li>
                <div>

                    <span class="label1"><?php esc_html_e('PHP Version:', 'pixel-gallery'); ?> </span>

                    <?php
                    if (version_compare($php_version, '7.0.0', '<')) {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s (Min: 7.0 Recommended)</span>', esc_html($php_version));
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s</span>', esc_html($php_version));
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('Maximum execution time:', 'pixel-gallery'); ?> </span>

                    <?php
                    if ($max_execution_time < '90') {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s ( Min: 90 Recommended)</span>', esc_html($max_execution_time));
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s</span>', esc_html($max_execution_time));
                    }
                    ?>
                </div>
            </li>
            <li>
                <div>
                    <span class="label1"><?php esc_html_e('Memory Limit:', 'pixel-gallery'); ?> </span>

                    <?php
                    if (intval($memory_limit) < '256') {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s (Min: 256M Recommended)</span>', esc_html($memory_limit));
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s</span>', esc_html($memory_limit));
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('Max Post Limit:', 'pixel-gallery'); ?> </span>

                    <?php
                    if (intval($post_limit) < '32') {
                        printf('<span class="invalid">%1$s</span>',  wp_kses($no_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s (Min: 32M Recommended)</span>', esc_html($post_limit));
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        printf('<span class="label2">Currently: %1$s</span>', esc_html($post_limit));
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('Uploads folder writable:', 'pixel-gallery'); ?></span>

                    <?php
                    if (!is_writable($upload_path)) {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('MultiSite: ', 'pixel-gallery'); ?></span>

                    <?php
                    if ($environment['wp_multisite']) {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        echo '<span class="label2">MultiSite</span>';
                    } else {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                        echo '<span class="label2">No MultiSite </span>';
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('GZip Enabled:', 'pixel-gallery'); ?></span>

                    <?php
                    if ($environment['gzip_enabled']) {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                    } else {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                    }
                    ?>
                </div>
            </li>

            <li>
                <div>
                    <span class="label1"><?php esc_html_e('Debug Mode: ', 'pixel-gallery'); ?></span>
                    <?php
                    if ($environment['wp_debug_mode']) {
                        printf('<span class="invalid">%1$s</span>', wp_kses($no_icon, $icon_validation));
                        echo '<span class="label2">Currently Turned On</span>';
                    } else {
                        printf('<span class="valid">%1$s</span>', wp_kses($yes_icon, $icon_validation));
                        echo '<span class="label2">Currently Turned Off</span>';
                    }
                    ?>
                </div>
            </li>

        </ul>

        <div class="bdt-admin-alert">
            <strong><?php esc_html_e('Note:', 'pixel-gallery'); ?></strong> <?php esc_html_e( 'If you have multiple addons like', 'pixel-gallery' ); ?> <strong><?php esc_html_e('Ultimate Store Kit', 'pixel-gallery'); ?></strong>
            <?php esc_html_e('so you need some more requirement some
      cases so make sure you added more memory for others addon too.', 'pixel-gallery'); ?>
        </div>
    <?php
    }


    /**
     * Display Plugin Page
     *
     * @access public
     * @return void
     */

    function plugin_page() {

        echo '<div class="wrap pixel-gallery-dashboard">';
        echo '<h1>' . esc_html(BDTPG_TITLE . ' ' . __('Settings', 'pixel-gallery')) . '</h1>';

        $this->settings_api->show_navigation();

    ?>


        <div class="bdt-switcher bdt-tab-container bdt-container-xlarge">
            <div id="pixel_gallery_welcome_page" class="pg-option-page group">
                <?php $this->pixel_gallery_welcome(); ?>

                <?php $this->footer_info(); ?>
            </div>

            <?php
            $this->settings_api->show_forms();
            ?>

            <?php if (_is_pg_pro_activated() !== true) : ?>
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

                <?php if (!defined('BDTPG_WL')) {
                    $this->footer_info();
                } ?>
            </div>


        </div>

        </div>

        <?php

        $this->script();

        ?>

    <?php
    }


    /**
     * Tabbable JavaScript codes & Initiate Color Picker
     *
     * This code uses localstorage for displaying active tabs
     */
    function script() {
    ?>
        <script>
            jQuery(document).ready(function() {
                jQuery('.pg-no-result').removeClass('bdt-animation-shake');
            });

            function filterSearch(e) {
                var parentID = '#' + jQuery(e).data('id');
                var search = jQuery(parentID).find('.bdt-search-input').val().toLowerCase();

                jQuery(".pg-options .pg-option-item").filter(function() {
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

            jQuery('.pg-options-parent').each(function(e, item) {
                var eachItem = '#' + jQuery(item).attr('id');
                jQuery(eachItem).on("beforeFilter", function() {
                    jQuery(eachItem).find('.pg-no-result').removeClass('bdt-animation-shake');
                });

                jQuery(eachItem).on("afterFilter", function() {

                    var isElementVisible = false;
                    var i = 0;

                    if (jQuery(eachItem).closest(".pg-options-parent").eq(i).is(":visible")) {} else {
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


            function clearSearchInputs(context) {
				context.find('.bdt-search-input').val('').attr('bdt-filter-control', '');
			}

			jQuery('.pg-widget-filter-nav li a').on('click', function () {
				const wrapper = jQuery(this).closest('.bdt-widget-filter-wrapper');
				clearSearchInputs(wrapper);
			});

			jQuery('.bdt-dashboard-navigation li a').on('click', function () {
				const tabContainer = jQuery(this).closest('.bdt-dashboard-navigation').siblings('.bdt-tab-container');
				clearSearchInputs(tabContainer);
					tabContainer.find('.bdt-search-input').trigger('keyup');
			});


            jQuery(document).ready(function($) {
                'use strict';

                function hashHandler() {
                    var $tab = jQuery('.pixel-gallery-dashboard .bdt-tab');
                    if (window.location.hash) {
                        var hash = window.location.hash.substring(1);
                        bdtUIkit.tab($tab).show(jQuery('#bdt-' + hash).data('tab-index'));
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

                jQuery('.toplevel_page_pixel_gallery_options > ul > li > a ').on('click', function(event) {
                    jQuery(this).parent().siblings().removeClass('current');
                    jQuery(this).parent().addClass('current');
                });

                jQuery('#pixel_gallery_active_modules_page a.pg-active-all-widget').on('click', function(e) {
                    e.preventDefault();

                    jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function() {
                        jQuery(this).attr('checked', 'checked').prop("checked", true);
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
                });

                jQuery('#pixel_gallery_active_modules_page a.pg-deactive-all-widget').on('click', function(e) {
                    e.preventDefault();

                    jQuery('#pixel_gallery_active_modules_page .pg-option-item:not(.pg-pro-inactive) .checkbox:visible').each(function() {
                        jQuery(this).removeAttr('checked');
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-active-all-widget').removeClass('bdt-active');
                });

                jQuery('#pixel_gallery_third_party_widget_page a.pg-active-all-widget').on('click', function() {

                    jQuery('#pixel_gallery_third_party_widget_page .checkbox:visible').each(function() {
                        jQuery(this).attr('checked', 'checked').prop("checked", true);
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
                });

                jQuery('#pixel_gallery_third_party_widget_page a.pg-deactive-all-widget').on('click', function() {

                    jQuery('#pixel_gallery_third_party_widget_page .checkbox:visible').each(function() {
                        jQuery(this).removeAttr('checked');
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-active-all-widget').removeClass('bdt-active');
                });

                jQuery('#pixel_gallery_elementor_extend_page a.pg-active-all-widget').on('click', function() {

                    jQuery('#pixel_gallery_elementor_extend_page .checkbox:visible').each(function() {
                        jQuery(this).attr('checked', 'checked').prop("checked", true);
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-deactive-all-widget').removeClass('bdt-active');
                });

                jQuery('#pixel_gallery_elementor_extend_page a.pg-deactive-all-widget').on('click', function() {

                    jQuery('#pixel_gallery_elementor_extend_page .checkbox:visible').each(function() {
                        jQuery(this).removeAttr('checked');
                    });

                    jQuery(this).addClass('bdt-active');
                    jQuery('a.pg-active-all-widget').removeClass('bdt-active');
                });

                jQuery('form.settings-save').on('submit', function(event) {
                    event.preventDefault();

                    bdtUIkit.notification({
                        message: '<div bdt-spinner></div> <?php esc_html_e('Please wait, Saving settings...', 'pixel-gallery') ?>',
                        timeout: false
                    });

                    jQuery(this).ajaxSubmit({
                        success: function() {
                            bdtUIkit.notification.closeAll();
                            bdtUIkit.notification({
                                message: '<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Settings Saved Successfully.', 'pixel-gallery') ?>',
                                status: 'primary'
                            });
                        },
                        error: function(data) {
                            bdtUIkit.notification.closeAll();
                            bdtUIkit.notification({
                                message: '<span bdt-icon=\'icon: warning\'></span> <?php esc_html_e('Unknown error, make sure access is correct!', 'pixel-gallery') ?>',
                                status: 'warning'
                            });
                        }
                    });

                    return false;
                });

                jQuery('#pixel_gallery_active_modules_page .pg-pro-inactive .checkbox').each(function() {
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

            jQuery(document).ready(function ($) {
                const renewalLink = $('a[href="admin.php?page=pixel_gallery_options_license_renew"]');
                if (renewalLink.length) {
                    renewalLink.attr('target', '_blank');
                }
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

    function footer_info() {
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
     * If Allow Tracker disable in elementor then this notice will be show
     *
     * @access public
     */

    public function allow_tracker_activate_notice() {
        Notices::add_notice(
            [
                'id'               => 'pg-allow-tracker',
                'type'             => 'warning',
                'category'         => 'critical',
                'dismissible'      => true,
                'dismissible-time' => WEEK_IN_SECONDS * 4,
                'message'          => __('Please activate <strong>Usage Data Sharing</strong> features from Elementor, otherwise Widgets Analytics will not work. Please activate the settings from <strong>Elementor > Settings > General Tab >  Usage Data Sharing.</strong> Thank you.', 'pixel-gallery'),
            ]
        );
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages         = get_pages();
        $pages_options = [];
        if ($pages) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }
}

new PixelGallery_Admin_Settings();
