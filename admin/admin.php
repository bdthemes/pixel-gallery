<?php

namespace PixelGallery;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


require_once BDTPG_ADMIN_PATH . 'class-settings-api.php';
// require_once BDTPG_ADMIN_PATH . 'admin-feeds.php';
// element pack admin settings here
require_once BDTPG_ADMIN_PATH . 'admin-settings.php';

/**
 * Admin class
 */

class Admin {

    public function __construct() {

        // Embed the Script on our Plugin's Option Page Only
        if (isset($_GET['page']) && ($_GET['page'] == 'pixel_gallery_options')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        }

        add_action('admin_init', [$this, 'admin_script']);

        add_action('upgrader_process_complete', [$this, 'pixel_gallery_plugin_on_upgrade_process_complete'], 10, 2);

        register_deactivation_hook(BDTPG__FILE__, [$this, 'pixel_gallery_plugin_on_deactivate']);

        add_action('after_setup_theme', [$this, 'whitelabel']);

        // register_activation_hook(BDTPG__FILE__, 'install_and_activate');

        add_action('admin_init', [$this, 'admin_notice_styles']);

        add_filter('plugin_action_links_' . BDTPG_PBNAME, [$this, 'plugin_action_links']);
        
    }

    public function admin_notice_styles(){
		wp_enqueue_style('pg-admin-notice', BDTPG_ADMIN_URL . 'assets/css/pg-admin-notice.css', [], BDTPG_VER);
	}


    function install_and_activate() {

        // I don't know of any other redirect function, so this'll have to do.
        wp_redirect(admin_url('admin.php?page=pixel_gallery_options'));
        // You could use a header(sprintf('Location: %s', admin_url(...)); here instead too.
    }

    /**
     * You can easily add white label branding for extended license or multi site license. Don't try for regular license otherwise your license will be invalid.
     * @return [type] [description]
     * Define BDTPG_WL for execute white label branding
     */
    public function whitelabel() {
        if (defined('BDTPG_WL')) {
            add_filter('gettext', [$this, 'pixel_gallery_name_change'], 20, 3);

            if (defined('BDTPG_HIDE')) {
                add_action('pre_current_active_plugins', [$this, 'hide_pixel_gallery']);
            }
        } else {
            add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);
            add_filter('plugin_action_links_' . BDTPG_PBNAME, [$this, 'plugin_action_meta']);
        }
    }

    /**
     * Enqueue styles
     * @access public
     */

    public function enqueue_styles() {

        $direction_suffix = is_rtl() ? '.rtl' : '';

        wp_enqueue_style('bdt-uikit', BDTPG_ADMIN_URL . 'assets/css/bdt-uikit' . $direction_suffix . '.css', [], '3.10.1');
        wp_enqueue_style('pg-editor', BDTPG_ASSETS_URL . 'css/pg-editor' . $direction_suffix . '.css', [], BDTPG_VER);
        wp_enqueue_style('pg-admin', BDTPG_ADMIN_URL . 'assets/css/pg-admin' . $direction_suffix . '.css', [], BDTPG_VER);


        wp_enqueue_script('bdt-uikit', BDTPG_ADMIN_URL . 'assets/js/bdt-uikit.min.js', ['jquery'], '3.10.1');
    }

    /**
     * Row meta
     * @access public
     * @return array
     */

    public function plugin_row_meta($plugin_meta, $plugin_file) {
        if (BDTPG_PBNAME === $plugin_file) {
            $row_meta = [
                'docs'  => '<a href="https://bdthemes.com/contact/" aria-label="' . esc_attr(__('Go for Get Support', 'pixel-gallery')) . '" target="_blank">' . __('Get Support', 'pixel-gallery') . '</a>',
                'video' => '<a href="https://www.youtube.com/playlist?list=PLP0S85GEw7DOJf_cbgUIL20qqwqb5x8KA" aria-label="' . esc_attr(__('View Pixel Gallery Video Tutorials', 'pixel-gallery')) . '" target="_blank">' . __('Video Tutorials', 'pixel-gallery') . '</a>',
            ];

            $plugin_meta = array_merge($plugin_meta, $row_meta);
        }

        return $plugin_meta;
    }

    /**
	 * Plugin action links
	 * @access public
	 * @return array
	 */

    public function plugin_action_links( $plugin_meta ) {

        if ( true !== _is_pg_pro_activated() ) {
            $row_meta = [
                'settings' => '<a href="'.admin_url( 'admin.php?page=pixel_gallery_options' ) .'" aria-label="' . esc_attr(__('Go to settings', 'pixel-gallery')) . '" >' . __('Settings', 'pixel-gallery') . '</b></a>',
                'gopro' => '<a href="https://pixelgallery.pro/pricing/?utm_source=PixelGallery&utm_medium=PluginPage&utm_campaign=30%OffOnPixelGallery&coupon=FREETOPRO" aria-label="' . esc_attr(__('Go get the pro version', 'pixel-gallery')) . '" target="_blank" title="When you purchase through this link you will get 30% discount!" class="pg-go-pro">' . __('Upgrade For 30% Off!', 'pixel-gallery') . '</a>',
            ];
        } else {
            $row_meta = [
                'settings' => '<a href="'.admin_url( 'admin.php?page=pixel_gallery_options' ) .'" aria-label="' . esc_attr(__('Go to settings', 'pixel-gallery')) . '" >' . __('Settings', 'pixel-gallery') . '</b></a>',
            ];
        }

        $plugin_meta = array_merge($plugin_meta, $row_meta);

        return $plugin_meta;
    }

    /**
     * Action meta
     * @access public
     * @return array
     */


    public function plugin_action_meta($links) {

        $links = array_merge([sprintf('<a href="%s">%s</a>', pixel_gallery_dashboard_link('#pixel_gallery_welcome'), esc_html__('Settings', 'pixel-gallery'))], $links);

        $links = array_merge($links, [
            sprintf(
                '<a href="%s">%s</a>',
                pixel_gallery_dashboard_link('#license'),
                esc_html__('License', 'pixel-gallery')
            )
        ]);

        return $links;
    }

    /**
     * Change Pixel Gallery Name
     * @access public
     * @return string
     */

    public function pixel_gallery_name_change($translated_text, $text, $domain) {
        switch ($translated_text) {
            case 'Pixel Gallery':
                $translated_text = BDTPG_TITLE;
                break;
        }

        return $translated_text;
    }

    /**
     * Hiding plugins //still in testing purpose
     * @access public
     */

    public function hide_pixel_gallery() {
        global $wp_list_table;
        $hide_plg_array = array('pixel-gallery/pixel-gallery.php');
        $all_plugins    = $wp_list_table->items;

        foreach ($all_plugins as $key => $val) {
            if (in_array($key, $hide_plg_array)) {
                unset($wp_list_table->items[$key]);
            }
        }
    }

    /**
     * Register admin script
     * @access public
     */

    public function admin_script() {
        
        if (is_admin()) { // for Admin Dashboard Only

            if (isset($_GET['page']) && ($_GET['page'] == 'pixel_gallery_options')) {
                wp_enqueue_script('chart', BDTPG_ADMIN_URL . 'assets/js/chart.min.js', ['jquery'], '2.7.3', true);
                wp_enqueue_script('pg-admin', BDTPG_ADMIN_URL  . 'assets/js/pg-admin.min.js', ['jquery', 'chart'], BDTPG_VER, true);
            }else{
                wp_enqueue_script('pg-admin', BDTPG_ADMIN_URL  . 'assets/js/pg-admin.min.js', ['jquery'], BDTPG_VER, true);
            }

            
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form');
            wp_enqueue_script('pg-notice', BDTPG_ADMIN_URL  . 'assets/js/pg-notice.min.js', ['jquery'], BDTPG_VER, true);
            
            $script_config = [
                'ajaxurl'	=> admin_url('admin-ajax.php'),
                'nonce'		=> wp_create_nonce('pixel-gallery'),
            ];

            wp_localize_script('pg-notice', 'PixelGalleryNoticeConfig', $script_config);

        }
    }

    /**
     * Drop Tables on deactivated plugin
     * @access public
     */

    public function pixel_gallery_plugin_on_deactivate() {

        global $wpdb;

        $table_cat      = $wpdb->prefix . 'pg_template_library_cat';
        $table_post     = $wpdb->prefix . 'pg_template_library_post';
        $table_cat_post = $wpdb->prefix . 'pg_template_library_cat_post';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_cat_post);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_cat);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_post);
    }

    /**
     * Upgrade Process Complete
     * @access public
     */

    public function pixel_gallery_plugin_on_upgrade_process_complete($upgrader_object, $options) {
        if (isset($options['action']) && $options['action'] == 'update' && $options['type'] == 'plugin') {
            if (isset($options['plugins']) && is_array($options['plugins'])) {
                foreach ($options['plugins'] as $each_plugin) {
                    if ($each_plugin == BDTPG_PBNAME) {
                        @$this->pixel_gallery_plugin_on_deactivate();
                    }
                }
            }
        }
    }
}
