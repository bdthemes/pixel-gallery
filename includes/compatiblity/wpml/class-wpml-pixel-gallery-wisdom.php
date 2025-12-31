<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Wisdom
 * Handles translation of repeater items in the Wisdom widget
 */
class WPML_Pixel_Gallery_Wisdom extends WPML_Module_With_Items {

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
            'date_day',
            'date_month',
            'text',
            'readmore_text',
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
            case 'date_day':
                return esc_html__( 'Day', 'pixel-gallery' );
            case 'date_month':
                return esc_html__( 'Month', 'pixel-gallery' );
            case 'text':
                return esc_html__( 'Text', 'pixel-gallery' );
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
            case 'date_day':
            case 'date_month':
            case 'readmore_text':
            case 'youtube_url':
            case 'vimeo_url':
            case 'dailymotion_url':
                return 'LINE';
            case 'text':
                return 'VISUAL';
            case 'link':
                return 'LINK';
            default:
                return 'LINE';
        }
    }
}

/**
 * Class WPML_Pixel_Gallery_Wisdom_Social_Link
 * Handles translation of repeater 'social_link_list' in the Ranch widget
 */
class WPML_Pixel_Gallery_Wisdom_Social_Link extends WPML_Module_With_Items {

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

