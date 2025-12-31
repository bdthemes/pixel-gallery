<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Tour
 * Handles translation of repeater items in the Tour widget
 */
class WPML_Pixel_Gallery_Tour extends WPML_Module_With_Items {

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
            'price',
            'meta_days',
            'meta_member',
            'meta_location',
            'link' => ['url'],
            'youtube_url',
            'vimeo_url',
            'dailymotion_url',
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
            case 'price':
                return esc_html__( 'Price', 'pixel-gallery' );
            case 'meta_days':
                return esc_html__( 'Meta Days', 'pixel-gallery' );
            case 'meta_member':
                return esc_html__( 'Meta Member', 'pixel-gallery' );
            case 'meta_location':
                return esc_html__( 'Meta Location', 'pixel-gallery' );
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
            case 'price':
            case 'meta_days':
            case 'meta_member':
            case 'meta_location':
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

