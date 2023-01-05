<?php

/**
 * Pixel Gallery widget filters
 * @since 5.7.4
 */

use PixelGallery\Admin\ModuleService;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Settings Filters
if (!function_exists('pg_is_dashboard_enabled')) {
    function pg_is_dashboard_enabled() {
        return apply_filters('pixel_gallery/settings/dashboard', true);
    }
}

if (!function_exists('pixel_gallery_is_widget_enabled')) {
    function pixel_gallery_is_widget_enabled($widget_id, $options = []) {

        if(!$options){
            $options = get_option('pixel_gallery_active_modules', []);
        }

        if( ModuleService::is_module_active($widget_id, $options)){
            $widget_id = str_replace('-','_', $widget_id);
            return apply_filters("pixel_gallery/widget/{$widget_id}", true);
        }
    }
}

if (!function_exists('pixel_gallery_is_extend_enabled')) {
    function pixel_gallery_is_extend_enabled($widget_id, $options = []) {

        if(!$options){
            $options = get_option('pixel_gallery_elementor_extend', []);
        }

        if( ModuleService::is_module_active($widget_id, $options)){
            $widget_id = str_replace('-','_', $widget_id);
            return apply_filters("pixel_gallery/extend/{$widget_id}", true);
        }
    }
}

if (!function_exists('pixel_gallery_is_third_party_enabled')) {
    function pixel_gallery_is_third_party_enabled($widget_id, $options = []) {

        if(!$options){
            $options = get_option('pixel_gallery_third_party_widget', []);
        }

        if( ModuleService::is_module_active($widget_id, $options)){
            $widget_id = str_replace('-','_', $widget_id);
            return apply_filters("pixel_gallery/widget/{$widget_id}", true);
        }
    }
}

if (!function_exists('pixel_gallery_is_asset_optimization_enabled')) {
    function pixel_gallery_is_asset_optimization_enabled() {
        $asset_manager = pixel_gallery_option('asset-manager', 'pixel_gallery_other_settings', 'off');
        if( $asset_manager == 'on'){
            return apply_filters("pixel_gallery/optimization/asset_manager", true);
        }
    }
}


