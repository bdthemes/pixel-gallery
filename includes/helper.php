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
 * Inject custom CSS and JS into the header
 */
if ( ! function_exists( 'pg_inject_header_custom_code' ) ) {
	function pg_inject_header_custom_code() {
		if ( pg_is_page_excluded() ) {
			return;
		}

		$custom_css = get_option( 'pg_custom_css', '' );
		$custom_js = get_option( 'pg_custom_js', '' );

		if ( ! empty( $custom_css ) ) {
			echo "\n<!-- Pixel Gallery Custom Header CSS -->\n";
			echo '<style type="text/css">' . "\n";
			echo $custom_css . "\n";
			echo '</style>' . "\n";
		}

		if ( ! empty( $custom_js ) ) {
			echo "\n<!-- Pixel Gallery Custom Header JS -->\n";
			echo '<script type="text/javascript">' . "\n";
			echo $custom_js . "\n";
			echo '</script>' . "\n";
		}
	}
}

/**
 * Inject custom CSS and JS into the footer
 */
if ( ! function_exists( 'pg_inject_footer_custom_code' ) ) {
	function pg_inject_footer_custom_code() {
		if ( pg_is_page_excluded() ) {
			return;
		}

		$custom_css_2 = get_option( 'pg_custom_css_2', '' );
		$custom_js_2 = get_option( 'pg_custom_js_2', '' );

		if ( ! empty( $custom_css_2 ) ) {
			echo "\n<!-- Pixel Gallery Custom Footer CSS -->\n";
			echo '<style type="text/css">' . "\n";
			echo $custom_css_2 . "\n";
			echo '</style>' . "\n";
		}

		if ( ! empty( $custom_js_2 ) ) {
			echo "\n<!-- Pixel Gallery Custom Footer JS -->\n";
			echo '<script type="text/javascript">' . "\n";
			echo $custom_js_2 . "\n";
			echo '</script>' . "\n";
		}
	}
}

/**
 * Check if current page should be excluded from custom code injection
 */
if ( ! function_exists( 'pg_is_page_excluded' ) ) {
	function pg_is_page_excluded() {
		$excluded_pages = get_option( 'pg_excluded_pages', array() );
		
		if ( empty( $excluded_pages ) || ! is_array( $excluded_pages ) ) {
			return false;
		}

		$current_id = 0;
		
		if ( is_home() && ! is_front_page() ) {
			$current_id = get_option( 'page_for_posts' );
		} elseif ( is_front_page() ) {
			$current_id = get_option( 'page_on_front' );
		} elseif ( is_singular() ) {
			$current_id = get_queried_object_id();
		} elseif ( is_category() || is_tag() || is_tax() ) {
			return false;
		} elseif ( is_author() ) {
			return false;
		} elseif ( is_archive() ) {
			return false;
		}

		return in_array( $current_id, $excluded_pages );
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
 * Get Pixel Gallery mask shapes options for VISUAL_CHOICE control
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

function pixel_gallery_post_pagination( $wp_query, $widget_id = '' ) {

	/** Stop execution if there's only 1 page */
	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	// Get current page from multiple sources for reliability
	$paged_from_query = isset( $wp_query->query_vars['paged'] ) ? $wp_query->query_vars['paged'] : 0;
	$page_from_query = isset( $wp_query->query_vars['page'] ) ? $wp_query->query_vars['page'] : 0;
	
	if ( is_front_page() ) {
		// On front page, WordPress can use either 'page' or 'paged' depending on permalink structure
		$paged = max( get_query_var( 'page' ), get_query_var( 'paged' ), $paged_from_query, $page_from_query );
		$paged = $paged ? $paged : 1;
		$page_var = 'page';
	} else {
		$paged = max( get_query_var( 'paged' ), $paged_from_query );
		$paged = $paged ? $paged : 1;
		$page_var = 'paged';
	}
	
	$max = intval( $wp_query->max_num_pages );

	/** Add current page to the array */
	if ( $paged >= 1 ) {
		$links[] = $paged;
	}

	/** Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}

	printf( '<ul class="pg-pagination" data-widget-id="%s">' . "\n", esc_attr($widget_id) );

	/** Previous Post Link */
	if ( $paged > 1 ) {
		$prev_page = $paged - 1;
		if ( is_front_page() && $prev_page == 1 ) {
			$prev_link = home_url( '/' );
		} else {
			$prev_link = get_pagenum_link( $prev_page );
		}
		printf( '<li class="pg-pagination-previous"><a href="%s" aria-label="' . esc_attr__( 'Previous Page', 'pixel-gallery' ) . '"><span data-pg-pagination-previous><i class="eicon-angle-left" aria-hidden="true"></i></span></a></li>' . "\n", esc_url( $prev_link ) );
	}

	/** Link to first page, plus ellipses if necessary */
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="current"' : '';
		
		// For page 1, always use home URL on front page, otherwise use get_pagenum_link
		if ( is_front_page() ) {
			$page_link = home_url( '/' );
		} else {
			$page_link = get_pagenum_link( 1 );
		}

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", wp_kses_post($class), esc_url( $page_link ), '1' );

		if ( ! in_array( 2, $links ) ) {
			echo '<li class="pg-pagination-dot-dot"><span>...</span></li>';
		}
	}

	/** Link to current page, plus 2 pages in either direction if necessary */
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="pg-active"' : '';
		
		// Use appropriate page link for front page vs other pages
		if ( is_front_page() && $link == 1 ) {
			$page_link = home_url( '/' );
		} else {
			$page_link = get_pagenum_link( $link );
		}
		
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", wp_kses_post($class), esc_url( $page_link ), wp_kses_post($link) );
	}

	/** Link to last page, plus ellipses if necessary */
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) ) {
			echo '<li class="pg-pagination-dot-dot"><span>...</span></li>' . "\n";
		}

		$class = $paged == $max ? ' class="pg-active"' : '';
		$page_link = get_pagenum_link( $max );
		
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", wp_kses_post($class), esc_url( $page_link ), wp_kses_post($max) );
	}

	/** Next Post Link */
	if ( $paged < $max ) {
		$next_page = $paged + 1;
		$next_link = get_pagenum_link( $next_page );
		printf( '<li class="pg-pagination-next"><a href="%s" aria-label="' . esc_attr__( 'Next Page', 'pixel-gallery' ) . '"><span data-pg-pagination-next><i class="eicon-angle-right" aria-hidden="true"></i></span></a></li>' . "\n", esc_url( $next_link ) );
	}

	echo '</ul>' . "\n";
}