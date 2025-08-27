<?php
//TODO: namespace need.  Note: We don't use namespace because use them easily
use Elementor\Plugin;

/**
 * You can easily add white label branding for for extended license or multi site license.
 * Don't try for regular license otherwise your license will be invalid.
 * return white label
 */
define('BDTPG_PNAME', basename(dirname(BDTPG__FILE__)));
define('BDTPG_PBNAME', plugin_basename(BDTPG__FILE__));
define('BDTPG_PATH', plugin_dir_path(BDTPG__FILE__));
define('BDTPG_URL', plugins_url('/', BDTPG__FILE__));
define('BDTPG_ADMIN_PATH', BDTPG_PATH . 'admin/');
define('BDTPG_ADMIN_URL', BDTPG_URL . 'admin/');
define('BDTPG_MODULES_PATH', BDTPG_PATH . 'modules/');
define('BDTPG_INC_PATH', BDTPG_PATH . 'includes/');
define('BDTPG_ASSETS_URL', BDTPG_URL . 'assets/');
define('BDTPG_ASSETS_PATH', BDTPG_PATH . 'assets/');
define('BDTPG_MODULES_URL', BDTPG_URL . 'modules/');

if (!defined('BDTPG')) {
    define('BDTPG', '');
} //Add prefix for all widgets <span class="bdt-widget-badge"></span>
if (!defined('BDTPG_CP')) {
    define('BDTPG_CP', '<span class="pg-widget-badge"></span>');
} //Add prefix for all widgets <span class="bdt-widget-badge"></span>
if (!defined('BDTPG_NC')) {
    define('BDTPG_NC', '<span class="pg-new-control"></span>');
} // if you have any custom style
if (!defined('BDTPG_SLUG')) {
    define('BDTPG_SLUG', 'pixel-gallery');
} // set your own alias

if (_is_pg_pro_activated()) {
	if (!defined('BDTPG_PC')) {
		define('BDTPG_PC', '');
	} // pro control badge
	define('BDTPG_IS_PC', '');
} else {
	if (!defined('BDTPG_PC')) {
		define('BDTPG_PC', '<span class="pg-pro-control"></span>');
	} // pro control badge
	define('BDTPG_IS_PC', 'pg-disabled-control');
}

function pixel_gallery_is_edit() {
    return Plugin::$instance->editor->is_edit_mode();
}

function pixel_gallery_is_preview() {
    return Plugin::$instance->preview->is_preview_mode();
}


/**
 * default get_option() default value check
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function pixel_gallery_option($option, $section, $default = '') {

    $options = get_option($section);

    if (isset($options[$option])) {
        return $options[$option];
    }

    return $default;
}


// BDT Blend Type
function pixel_gallery_blend_options() {
    $blend_options = [
        'multiply'    => esc_html__('Multiply', 'pixel-gallery'),
        'screen'      => esc_html__('Screen', 'pixel-gallery'),
        'overlay'     => esc_html__('Overlay', 'pixel-gallery'),
        'darken'      => esc_html__('Darken', 'pixel-gallery'),
        'lighten'     => esc_html__('Lighten', 'pixel-gallery'),
        'color-dodge' => esc_html__('Color-Dodge', 'pixel-gallery'),
        'color-burn'  => esc_html__('Color-Burn', 'pixel-gallery'),
        'hard-light'  => esc_html__('Hard-Light', 'pixel-gallery'),
        'soft-light'  => esc_html__('Soft-Light', 'pixel-gallery'),
        'difference'  => esc_html__('Difference', 'pixel-gallery'),
        'exclusion'   => esc_html__('Exclusion', 'pixel-gallery'),
        'hue'         => esc_html__('Hue', 'pixel-gallery'),
        'saturation'  => esc_html__('Saturation', 'pixel-gallery'),
        'color'       => esc_html__('Color', 'pixel-gallery'),
        'luminosity'  => esc_html__('Luminosity', 'pixel-gallery'),
    ];

    return $blend_options;
}


// Title Tags
function pixel_gallery_title_tags() {
    $title_tags = [
        'h1'   => 'H1',
        'h2'   => 'H2',
        'h3'   => 'H3',
        'h4'   => 'H4',
        'h5'   => 'H5',
        'h6'   => 'H6',
        'div'  => 'div',
        'span' => 'span',
        'p'    => 'p',
    ];

    return $title_tags;
}


/**
 * [pixel_gallery_dashboard_link description]
 * @param  string $suffix [description]
 * @return [type]         [description]
 */
function pixel_gallery_dashboard_link($suffix = '#welcome') {
    return add_query_arg(['page' => 'pixel_gallery_options' . $suffix], admin_url('admin.php'));
}


/**
 * @param $post_type string any post type that you want to show category
 * @param $separator string separator for multiple category
 *
 * @return string
 */
function pixel_gallery_get_category_list($post_type, $separator = ' ') {
    switch ($post_type) {
        case 'campaign':
            $taxonomy = 'campaign_category';
            break;
        case 'lightbox_library':
            $taxonomy = 'ngg_tag';
            break;
        case 'give_forms':
            $taxonomy = 'give_forms_category';
            break;
        case 'tribe_events':
            $taxonomy = 'tribe_events_cat';
            break;
        case 'product':
            $taxonomy = 'product_cat';
            break;
        case 'portfolio':
            $taxonomy = 'portfolio_filter';
            break;
        case 'faq':
            $taxonomy = 'faq_filter';
            break;
        case 'bdthemes-testimonial':
            $taxonomy = 'testimonial_categories';
            break;
        case 'knowledge_base':
            $taxonomy = 'knowledge-type';
            break;
        default:
            $taxonomy = 'category';
            break;
    }

    $categories  = get_the_terms(get_the_ID(), $taxonomy);
    $_categories = [];
    if ($categories && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            // Ensure $category is an object, not an array
            if (is_object($category) && isset($category->term_id, $category->name, $category->slug)) {
                $link                         = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . $category->name . '</a>';
                $_categories[$category->slug] = $link;
            }
        }
    }
    return implode(esc_attr($separator), $_categories);
}


/**
 * License Validation
 */
if (!function_exists('pg_license_validation')) {
    function pg_license_validation() {

        if (function_exists('_is_pg_pro_activated') && false === _is_pg_pro_activated()) {
            return false;
        }

        $license_key   = trim(get_option('pixel_gallery_license_key'));

        if (isset($license_key) && !empty($license_key)) {
            return true;
        } else {
            return false;
        }
        return false;
    }
}

/**
 * Mask Shapes 
 */

function pixel_gallery_mask_shapes() {
    $shape_name = 'shape';
	$list       = [];

	for ( $i = 1; $i <= 31; $i++ ) {
		$list[ $shape_name . '-' . $i ] = ucwords( $shape_name . ' ' . $i );
	}

	return $list;
}

/**
 * Get Element Pack mask shapes options for VISUAL_CHOICE control
 * 
 * @return array Options array for VISUAL_CHOICE control
 */
function pixel_gallery_mask_shapes_options() {
	$options = [];
	$shape_list = pixel_gallery_mask_shapes();
	
	foreach ( $shape_list as $shape_key => $shape_name ) {
		// Skip the first item if it's a placeholder
		if ( $shape_key === 0 ) {
			continue;
		}
		
		$options[ $shape_key ] = [
			'title' => $shape_name,
			'image' => BDTPG_ASSETS_URL . 'images/mask/' . $shape_key . '.svg',
		];
	}
	
	return $options;
}