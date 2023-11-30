<?php

namespace PixelGallery;

use PixelGallery\Admin\ModuleService;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class Manager {


    public function register_module_and_assets() {

        ModuleService::get_widget_settings(function ($settings) {
            $core_widgets        = $settings['settings_fields']['pixel_gallery_active_modules'];
            $extensions          = $settings['settings_fields']['pixel_gallery_elementor_extend'];
            // $third_party_widgets = $settings['settings_fields']['pixel_gallery_third_party_widget'];

            /**
             * Our Widget
             */
            foreach ($core_widgets as $widget) {
                if (pixel_gallery_is_widget_enabled($widget['name'])) {
                    $this->load_module_instance($widget);
                }
            }

            /**
             * Extension
             */
            foreach ( $extensions as $extension ) {
                if ( pixel_gallery_is_extend_enabled($extension['name']) ) {
                    $this->load_module_instance($extension);
                }
            }

            /**
             * Third Party Widget
             */
            // foreach ($third_party_widgets as $widget) {
            //     if (pixel_gallery_is_third_party_enabled($widget['name'])) {
            //         if (isset($widget['plugin_path']) && ModuleService::is_plugin_active($widget['plugin_path'])) {
            //             $this->load_module_instance($widget);
            //         }
            //     }
            // }
            // Static module if need
            $this->load_module_instance(['name' => 'elementor']);
        });
    }

    public function load_module_instance($module) {

        $direction = is_rtl() ? '.rtl' : '';

        $module_id  = $module['name'];
        $class_name = str_replace('-', ' ', $module_id);
        $class_name = str_replace(' ', '', ucwords($class_name));
        $class_name = __NAMESPACE__ . '\\Modules\\' . $class_name . '\\Module';

        if (!pixel_gallery_is_asset_optimization_enabled()) {
            if (!pixel_gallery_is_preview()) {
                // register widgets css
                if (ModuleService::has_module_style($module_id)) {
                    wp_register_style('pg-' . $module_id, BDTPG_URL . 'assets/css/pg-' . $module_id . $direction . '.css', [], BDTPG_VER);
                }
                // register widget JS
                if (ModuleService::has_module_script($module_id)) {
                    wp_register_script('pg-' . $module_id, BDTPG_URL . 'assets/js/modules/pg-' . $module_id . '.min.js', ['jquery', 'elementor-frontend'], BDTPG_VER, true);
                }
            }
        }

        if (class_exists($class_name)) {
            $class_name::instance();
        }
    }

    public function __construct() {

        $this->register_module_and_assets();
    }
}
