<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Remix
 * Handles translation of repeater items in the Remix widget
 */
class WPML_Pixel_Gallery_Remix extends WPML_Module_With_Items {

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
            'readmore_text',
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
            case 'readmore_text':
                return esc_html__( 'Read More', 'pixel-gallery' );
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

