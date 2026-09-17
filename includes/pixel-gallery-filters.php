<?php

/**
 * Pixel Gallery widget filters
 * @since 5.7.4
 */

use PixelGallery\Admin\ModuleService;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Settings Filters
if (!function_exists('pixel_gallery_is_dashboard_enabled')) {
    function pixel_gallery_is_dashboard_enabled() {
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
    /**
     * The Asset Manager has been removed, so asset optimization is never enabled.
     *
     * Kept because released Pixel Gallery Pro versions still call this on every
     * frontend request; removing it would fatal those sites.
     *
     * @deprecated
     * @return bool Always false.
     */
    function pixel_gallery_is_asset_optimization_enabled() {
        return false;
    }
}


