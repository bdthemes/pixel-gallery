<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Ranch
 * Handles translation of repeater items in the Ranch widget
 */
class WPML_Pixel_Gallery_Ranch extends WPML_Module_With_Items {

    /**
     * @return string
     */
    public function get_items_field() {
        return 'items';
    }

    /**
     * @return array
     */
    public function get_fields() {
        return array(
            'title',
            'meta',
            'link' => ['url'],
            'youtube_url',
            'vimeo_url',
            'dailymotion_url'
        );
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_title( $field ) {
        switch ( $field ) {
            case 'title':
                return esc_html__( 'Title', 'pixel-gallery' );
            case 'meta':
                return esc_html__( 'Meta', 'pixel-gallery' );
            case 'link':
                return esc_html__( 'Custom URL', 'pixel-gallery' );
            case 'youtube_url':
                return esc_html__( 'YouTube URL', 'pixel-gallery' );
            case 'vimeo_url':
                return esc_html__( 'Vimeo URL', 'pixel-gallery' );
            case 'dailymotion_url':
                return esc_html__( 'Dailymotion URL', 'pixel-gallery' );
            default:
                return '';
        }
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_editor_type( $field ) {
        switch ( $field ) {
            case 'title':
            case 'meta':
            case 'youtube_url':
            case 'vimeo_url':
            case 'dailymotion_url':
                return 'LINE';
            case 'link':
                return 'LINK';
            default:
                return 'LINE';
        }
    }
}

/**
 * Class WPML_Pixel_Gallery_Ranch_Social_Link
 * Handles translation of repeater 'social_link_list' in the Ranch widget
 */
class WPML_Pixel_Gallery_Ranch_Social_Link extends WPML_Module_With_Items {

    /**
     * @return string
     */
    public function get_items_field() {
        return 'social_link_list';
    }

    /**
     * @return array
     */
    public function get_fields() {
        return array(
            'social_link_title',
            'social_link',
        );
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_title( $field ) {
        switch ( $field ) {
            case 'social_link_title':
                return esc_html__( 'Social Link Title', 'bdthemes-prime-slider' );
            case 'social_link':
                return esc_html__( 'Social Link URL', 'bdthemes-prime-slider' );
            default:
                return '';
        }
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_editor_type( $field ) {
        switch ( $field ) {
            case 'social_link_title':
            case 'social_link':
                return 'LINE';
            default:
                return 'LINE';
        }
    }
}

