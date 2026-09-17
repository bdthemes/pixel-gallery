<?php

namespace PixelGallery;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


require_once BDTPG_ADMIN_PATH . 'class-settings-api.php';
// require_once BDTPG_ADMIN_PATH . 'admin-feeds.php';
// pixel gallery admin settings here
require_once BDTPG_ADMIN_PATH . 'admin-settings.php';

/**
 * Admin class
 */

class Admin {

    public function __construct() {

        // Embed the Script on our Plugin's Option Page Only
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the page query var for admin menu routing only, no state change.
        if (isset($_GET['page']) && ('pixel_gallery_options' === sanitize_text_field(wp_unslash($_GET['page'])))) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        }

        add_action('admin_init', [$this, 'admin_script']);

        add_action('upgrader_process_complete', [$this, 'pixel_gallery_plugin_on_upgrade_process_complete'], 10, 2);

        register_deactivation_hook(BDTPG__FILE__, [$this, 'pixel_gallery_plugin_on_deactivate']);

        // This class is created on "init", after "after_setup_theme" has already run,
        // so register the row links directly.
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);

        add_filter('plugin_action_links_' . BDTPG_PBNAME, [$this, 'plugin_action_links']);
        
    }


    function install_and_activate() {

        // I don't know of any other redirect function, so this'll have to do.
        wp_safe_redirect(admin_url('admin.php?page=pixel_gallery_options'));
        exit;
        // You could use a header(sprintf('Location: %s', admin_url(...)); here instead too.
    }

    /**
     * Enqueue styles
     * @access public
     */

    public function enqueue_styles() {

        $direction_suffix = is_rtl() ? '.rtl' : '';

        wp_enqueue_style('bdt-uikit', BDTPG_ADMIN_URL . 'assets/css/bdt-uikit'. $direction_suffix .'.css', [], BDTPG_VER);
        wp_enqueue_style('pg-editor', BDTPG_ASSETS_URL . 'css/pg-editor.css', [], BDTPG_VER);
        wp_enqueue_style('pg-admin', BDTPG_ADMIN_URL . 'assets/css/pg-admin.css', [], BDTPG_VER);


        wp_enqueue_script('bdt-uikit', BDTPG_ADMIN_URL . 'assets/js/bdt-uikit.min.js', ['jquery'], '3.25.22', true);
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
                'video' => '<a href="https://www.youtube.com/playlist?list=PLP0S85GEw7DPv5T-Ara11Zvplmk4ty0jy" aria-label="' . esc_attr(__('View Pixel Gallery Video Tutorials', 'pixel-gallery')) . '" target="_blank">' . __('Video Tutorials', 'pixel-gallery') . '</a>',
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

        $row_meta = [
            'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=pixel_gallery_options' ) ) . '" aria-label="' . esc_attr__( 'Go to settings', 'pixel-gallery' ) . '">' . esc_html__( 'Settings', 'pixel-gallery' ) . '</a>',
        ];

        $plugin_meta = array_merge($plugin_meta, $row_meta);

        return $plugin_meta;
    }

    /**
     * Register admin script
     * @access public
     */

    public function admin_script() {
        
        if (is_admin()) { // for Admin Dashboard Only

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the page query var for admin menu routing only, no state change.
            if (isset($_GET['page']) && ('pixel_gallery_options' === sanitize_text_field(wp_unslash($_GET['page'])))) {
                wp_enqueue_script('bdtpg-chart', BDTPG_ADMIN_URL . 'assets/js/chart.min.js', ['jquery'], '4.5.1', true);
                wp_enqueue_script('pg-admin', BDTPG_ADMIN_URL  . 'assets/js/pg-admin.min.js', ['jquery', 'bdtpg-chart'], BDTPG_VER, true);
            }else{
                wp_enqueue_script('pg-admin', BDTPG_ADMIN_URL  . 'assets/js/pg-admin.min.js', ['jquery'], BDTPG_VER, true);
            }

            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-form');

            wp_enqueue_style('bdt-product-feed', BDTPG_ADMIN_URL . 'assets/css/pg-admin-feeds.css', [], BDTPG_VER);

            wp_enqueue_script('pg-biggopti', BDTPG_ADMIN_URL  . 'assets/js/pg-biggopti.min.js', ['jquery'], BDTPG_VER, true);

            $dismissals = get_option('bdt_biggopti_dismissals', []);
			$dismissed_display_ids = [];
			$prefix = 'bdt-admin-biggopti-api-biggopti-';
			foreach (array_keys($dismissals) as $key) {
				if (strpos($key, $prefix) === 0) {
					$dismissed_display_ids[] = substr($key, strlen($prefix));
				} else {
					$dismissed_display_ids[] = $key;
				}
			}

			$current_sector = '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading the page query var for admin menu routing only, no state change.
			if ( isset( $_GET['page'] ) && 'pixel_gallery_options' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
				$current_sector = 'plugin_dashboard';
			}
            
            $script_config = [
                'ajaxurl'	=> admin_url('admin-ajax.php'),
                'nonce'		=> wp_create_nonce('pixel-gallery'),
                'isPro'             	=> function_exists('_is_pg_pro_activated') && _is_pg_pro_activated(),
				'assetsUrl'         	=> defined('BDTPG_ASSETS_URL') ? BDTPG_ASSETS_URL : '',
				'dismissedDisplayIds'	=> $dismissed_display_ids,
				'currentSector'      	=> $current_sector,
            ];

            wp_localize_script('pg-biggopti', 'PixelGalleryBiggoptiConfig', $script_config);

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
        // Removing the plugin's own tables on deactivation is a one-time schema
        // change; there is nothing to cache and no core API for dropping tables.
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_cat_post); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Table name derived from $wpdb->prefix, not user input.
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_cat); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Table name derived from $wpdb->prefix, not user input.
        $wpdb->query('DROP TABLE IF EXISTS ' . $table_post); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Table name derived from $wpdb->prefix, not user input.
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
