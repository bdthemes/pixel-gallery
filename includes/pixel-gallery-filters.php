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

if (!function_exists('pixel_gallery_is_asset_combining_enabled')) {
    /**
     * Whether Asset Optimization (Pixel Gallery > Performance) is switched on.
     *
     * With it on, the widget CSS and JS files a page loads are joined into one
     * combined stylesheet and one combined script for that page; see
     * PixelGallery\Includes\Asset_Combiner.
     *
     * Deliberately not pixel_gallery_is_asset_optimization_enabled(): released
     * Pixel Gallery Pro versions read that one and, when it is true, enqueue the
     * bundles of the old Asset Manager, which no longer exist.
     *
     * @return bool
     */
    function pixel_gallery_is_asset_combining_enabled() {
        $enabled = ('on' === pixel_gallery_option('asset-manager', 'pixel_gallery_other_settings', 'off'));

        return (bool) apply_filters('pixel_gallery/optimization/asset_manager', $enabled);
    }
}

if (!function_exists('pixel_gallery_module_asset_depends')) {
    /**
     * The shared handles every widget stylesheet or script depends on.
     *
     * The helper stylesheet (`pg-helper`, which pulls Elementor's Swiper CSS) and
     * the icon font are not loaded on every page: a page without a Pixel Gallery
     * widget has no use for them. Every `pg-<module>` stylesheet depends on them
     * instead, so a page loads them exactly when it loads a widget.
     *
     * @param string $type      'css' or 'js'.
     * @param string $module_id Module slug.
     * @return string[]
     */
    function pixel_gallery_module_asset_depends($type, $module_id = '') {
        $depends = 'js' === $type ? ['jquery', 'elementor-frontend'] : ['pg-helper', 'pg-font'];

        /**
         * Filters the handles a Pixel Gallery widget stylesheet or script depends on.
         *
         * @param string[] $depends   Handles the module file depends on.
         * @param string   $type      'css' or 'js'.
         * @param string   $module_id Module slug.
         */
        return (array) apply_filters('pixel_gallery/module/asset_depends', $depends, $type, $module_id);
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


