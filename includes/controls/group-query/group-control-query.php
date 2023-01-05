<?php

namespace PixelGallery\Includes\Controls\GroupQuery;

use Elementor\Controls_Manager;
use PixelGallery\Includes\Controls\SelectInput\Dynamic_Select;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

trait Group_Control_Query {

	public function register_query_builder_controls() {

		$this->add_control(
			'posts_source',
			[
				'label'   => __('Source', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->getGroupControlQueryPostTypes(),
				'default' => 'post',

			]
		);

		// TODO for next major update
		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__('Limit', 'pixel-gallery'),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_control(
			'posts_selected_ids',
			[
				'label'       => __('Search & Select', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'query_args'  => [
					'query' => 'posts',
				],
				'condition'   => [
					'posts_source' => 'manual_selection',
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_posts_include_exclude',
			[
				'condition' => [
					'posts_source!' => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->start_controls_tab(
			'tab_posts_include',
			[
				'label'     => __('Include', 'pixel-gallery'),
				'condition' => [
					'posts_source!' => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_include_by',
			[
				'label'       => __('Include By', 'pixel-gallery'),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => [
					'authors' => __('Authors', 'pixel-gallery'),
					'terms'   => __('Terms', 'pixel-gallery'),
				],
				'condition'   => [
					'posts_source!' => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_include_author_ids',
			[
				'label'       => __('Authors', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'query_args'  => [
					'query' => 'authors',
				],
				'condition'   => [
					'posts_include_by' => 'authors',
					'posts_source!'    => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_include_term_ids',
			[
				'label'       => __('Terms', 'pixel-gallery'),
				'description' => __('Terms are items in a taxonomy. The available taxonomies are: Categories, Tags, Formats and custom taxonomies.', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __('Type and select terms', 'pixel-gallery'),
				'query_args'  => [
					'query'        => 'terms',
					'widget_props' => [
						'post_type' => 'posts_source',
					],
				],
				'condition'   => [
					'posts_include_by' => 'terms',
					'posts_source!'    => ['manual_selection', 'current_query'], // , '_related_post_type'
				],
			]
		);

		/**
		 * ! TODO FOR NEXT DAY ADDED BY TALIB
		 */

		// $this->update_control(
		// 	'posts_include_term_ids',
		// 	[
		// 		'label'     => __('Terms', 'pixel-gallery'),
		// 		'type'      => Controls_Manager::SELECT2,
		// 		'multiple'  => true,
		// 		'label_block' => true,
		// 		'options'   => $this->pg_get_supported_taxonomies(),
		// 		'condition' => [
		// 			'posts_include_by'   => 'terms',
		// 			'posts_source' => [
		// 				'_related_post_type',
		// 			],
		// 		],
		// 	]
		// );

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_posts_exclude',
			[
				'label'     => __('Exclude', 'pixel-gallery'),
				'condition' => [
					'posts_source!' => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_exclude_by',
			[
				'label'       => __('Exclude By', 'pixel-gallery'),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => [
					'authors'          => __('Authors', 'pixel-gallery'),
					'current_post'     => __('Current Post', 'pixel-gallery'),
					'manual_selection' => __('Manual Selection', 'pixel-gallery'),
					'terms'            => __('Terms', 'pixel-gallery'),
				],
				'condition'   => [
					'posts_source!' => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_exclude_ids',
			[
				'label'       => __('Search & Select', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'query_args'  => [
					'query'        => 'posts',
					'widget_props' => [
						'post_type' => 'posts_source',
					],
				],
				'condition'   => [
					'posts_source!'    => ['manual_selection', 'current_query'],
					'posts_exclude_by' => 'manual_selection',
				],
			]
		);

		$this->add_control(
			'posts_exclude_author_ids',
			[
				'label'       => __('Authors', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'query_args'  => [
					'query' => 'authors',
				],
				'condition'   => [
					'posts_exclude_by' => 'authors',
					'posts_source!'    => ['manual_selection', 'current_query'],
				],
			]
		);

		$this->add_control(
			'posts_exclude_term_ids',
			[
				'label'       => __('Terms', 'pixel-gallery'),
				'description' => __('Terms are items in a taxonomy. The available taxonomies are: Categories, Tags, Formats and custom taxonomies.', 'pixel-gallery'),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __('Type and select terms', 'pixel-gallery'),
				'query_args'  => [
					'query'        => 'terms',
					'widget_props' => [
						'post_type' => 'posts_source',
					],
				],
				'condition'   => [
					'posts_exclude_by' => 'terms',
					'posts_source!'    => ['manual_selection', 'current_query', '_related_post_type'],
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'posts_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'condition' => [
					'posts_source!' => 'current_query',
				],
			]
		);
		$this->add_control(
			'posts_offset',
			[
				'label'   => __('Offset', 'pixel-gallery') . BDTPG_NC,
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,

			]
		);

		$this->add_control(
			'posts_select_date',
			[
				'label'     => __('Date', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'anytime',
				'options'   => [
					'anytime' => __('All', 'pixel-gallery'),
					'today'   => __('Past Day', 'pixel-gallery'),
					'week'    => __('Past Week', 'pixel-gallery'),
					'month'   => __('Past Month', 'pixel-gallery'),
					'quarter' => __('Past Quarter', 'pixel-gallery'),
					'year'    => __('Past Year', 'pixel-gallery'),
					'exact'   => __('Custom', 'pixel-gallery'),
				],
				'condition' => [
					'posts_source!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'posts_date_before',
			[
				'label'       => __('Before', 'pixel-gallery'),
				'type'        => Controls_Manager::DATE_TIME,
				'description' => __('Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'pixel-gallery'),
				'condition'   => [
					'posts_select_date' => 'exact',
					'posts_source!'     => 'current_query',
				],
			]
		);

		$this->add_control(
			'posts_date_after',
			[
				'label'       => __('After', 'pixel-gallery'),
				'type'        => Controls_Manager::DATE_TIME,
				'description' => __('Setting an ‘After’ date will show all the posts published since the chosen date (inclusive).', 'pixel-gallery'),
				'condition'   => [
					'posts_select_date' => 'exact',
					'posts_source!'     => 'current_query',
				],
			]
		);

		$this->add_control(
			'posts_orderby',
			[
				'label'     => __('Order By', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'title'         => __('Title', 'pixel-gallery'),
					'ID'            => __('ID', 'pixel-gallery'),
					'date'          => __('Date', 'pixel-gallery'),
					'author'        => __('Author', 'pixel-gallery'),
					'comment_count' => __('Comment Count', 'pixel-gallery'),
					'menu_order'    => __('Menu Order', 'pixel-gallery'),
					'rand'          => __('Random', 'pixel-gallery'),
				],
				'condition' => [
					'posts_source!' => ['current_query', 'product'],
				],
			]
		);
		$this->add_control(
			'posts_order',
			[
				'label'     => __('Order', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'desc',
				'options'   => [
					'asc'  => __('ASC', 'pixel-gallery'),
					'desc' => __('DESC', 'pixel-gallery'),
				],
				'condition' => [
					'posts_source!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'posts_ignore_sticky_posts',
			[
				'label'        => __('Ignore Sticky Posts', 'pixel-gallery'),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'posts_source' => ['post'],
				],
			]
		);

		$this->add_control(
			'posts_only_with_featured_image',
			[
				'label'        => __('Only Featured Image Post', 'pixel-gallery'),
				'description'  => __('Enable to display posts only when featured image is present.', 'pixel-gallery'),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition'    => [
					'posts_source!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'query_id',
			[
				'label'       => __('Query ID', 'pixel-gallery'),
				'description' => __('Give your Query a custom unique id to allow server side filtering', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'separator'   => 'before',
			]
		);
	}

	public function register_wc_query_additional($per_page = '8') {
		$this->update_control(
			'posts_source',
			[
				'type'    => Controls_Manager::SELECT,
				'default' => 'product',
				'options' => [
					'product'            => "Product",
					'manual_selection'   => __('Manual Selection', 'pixel-gallery'),
					'current_query'      => __('Current Query', 'pixel-gallery'),
					'_related_post_type' => __('Related', 'pixel-gallery'),
				],
			]
		);

		$this->update_control(
			'posts_per_page',
			[
				'default' => $per_page,
			]
		);
		$this->update_control(
			'posts_orderby',
			[
				'label'   => __('Order By', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'title'         => __('Title', 'pixel-gallery'),
					'ID'            => __('ID', 'pixel-gallery'),
					'date'          => __('Date', 'pixel-gallery'),
					'author'        => __('Author', 'pixel-gallery'),
					'comment_count' => __('Comment Count', 'pixel-gallery'),
					'menu_order'    => __('Menu Order', 'pixel-gallery'),
					'rand'          => __('Random', 'pixel-gallery'),
					'price'         => __('Price', 'pixel-gallery'),
					'sales'         => __('Sales', 'pixel-gallery'),
				],

			]
		);
		$this->add_control(
			'product_show_product_type',
			[
				'label'     => esc_html__('Show Product', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => [
					'all'      => esc_html__('All Products', 'pixel-gallery'),
					'onsale'   => esc_html__('On Sale', 'pixel-gallery'),
					'featured' => esc_html__('Featured', 'pixel-gallery'),
				],
				'condition' => [
					'posts_source!' => 'current_query',
				],
				'separator' => 'before',
			]
		);
		$this->add_control(
			'product_hide_free',
			[
				'label'     => esc_html__('Hide Free Product', 'pixel-gallery'),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'posts_source!' => 'current_query',
				],
			]
		);
		$this->add_control(
			'product_hide_out_stock',
			[
				'label'     => esc_html__('Hide Out of Stock', 'pixel-gallery'),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'posts_source!' => 'current_query',
				],
			]
		);
	}

	private function setMetaQueryArgs() {

		$args = [];

		if ('current_query' === $this->getGroupControlQueryPostType()) {
			return [];
		}

		$args['order']   = $this->get_settings_for_display('posts_order');
		$args['orderby'] = $this->get_settings_for_display('posts_orderby');

		/**
		 * Set Feature Images
		 */

		if ($this->get_settings_for_display('posts_only_with_featured_image') === 'yes') {
			$args['meta_key'] = '_thumbnail_id';
		}

		/**
		 * Set Date
		 */

		$selected_date = $this->get_settings_for_display('posts_select_date');

		if (!empty($selected_date)) {
			$date_query = [];

			switch ($selected_date) {
				case 'today':
					$date_query['after'] = '-1 day';
					break;

				case 'week':
					$date_query['after'] = '-1 week';
					break;

				case 'month':
					$date_query['after'] = '-1 month';
					break;

				case 'quarter':
					$date_query['after'] = '-3 month';
					break;

				case 'year':
					$date_query['after'] = '-1 year';
					break;

				case 'exact':
					$after_date = $this->get_settings_for_display('posts_date_after');

					if (!empty($after_date)) {
						$date_query['after'] = $after_date;
					}

					$before_date = $this->get_settings_for_display('posts_date_before');

					if (!empty($before_date)) {
						$date_query['before'] = $before_date;
					}

					$date_query['inclusive'] = true;
					break;
			}

			if (!empty($date_query)) {
				$args['date_query'] = $date_query;
			}
		}

		return $args;
	}

	protected function getGroupControlQueryArgs() {

		$settings = $this->get_settings_for_display();
		$args     = $this->setMetaQueryArgs();

		$args['post_status']      = 'publish';
		$args['suppress_filters'] = false;
		$exclude_by               = $this->getGroupControlQueryParamBy('exclude');

		if (0 < $settings['posts_offset']) {
			$args['offset_to_fix'] = $settings['posts_offset'];
		}

		/**
		 * Set Ignore Sticky
		 */
		if (
			$this->getGroupControlQueryPostType() === 'post'
			&& $this->get_settings_for_display('posts_ignore_sticky_posts') === 'yes'
		) {
			$args['ignore_sticky_posts'] = true;

			if (in_array('current_post', $exclude_by)) {
				$args['post__not_in'] = [get_the_ID()];
			}
		}

		if ($this->getGroupControlQueryPostType() === 'manual_selection') {
			/**
			 * Set Including Manually
			 */
			$selected_ids      = $this->get_settings_for_display('posts_selected_ids');
			$selected_ids      = wp_parse_id_list($selected_ids);
			$args['post_type'] = 'any';
			if (!empty($selected_ids)) {
				$args['post__in'] = $selected_ids;
			}

			$args['ignore_sticky_posts'] = 1;
		} elseif ('current_query' === $this->getGroupControlQueryPostType()) {
			/**
			 * Make Current Query
			 */
			$args = $GLOBALS['wp_query']->query_vars;
			$args = apply_filters('pixel_gallery/query/get_query_args/current_query', $args);
		} elseif ('_related_post_type' === $this->getGroupControlQueryPostType()) {
			/**
			 * Set Related Query
			 */
			$post_id           = get_queried_object_id();
			$related_post_id   = is_singular() && (0 !== $post_id) ? $post_id : null;
			$args['post_type'] = get_post_type($related_post_id);

			$include_by = $this->getGroupControlQueryParamBy('include');
			if (in_array('authors', $include_by)) {
				$args['author__in'] = wp_parse_id_list($settings['posts_include_author_ids']);
			} else {
				$args['author__in'] = get_post_field('post_author', $related_post_id);
			}

			$exclude_by = $this->getGroupControlQueryParamBy('exclude');
			if (in_array('authors', $exclude_by)) {
				$args['author__not_in'] = wp_parse_id_list($settings['posts_exclude_author_ids']);
			}

			if (in_array('current_post', $exclude_by)) {
				$args['post__not_in'] = [get_the_ID()];
			}

			$args['ignore_sticky_posts'] = 1;
			$args                        = apply_filters('pixel_gallery/query/get_query_args/related_query', $args);
		} else {

			/**
			 * Set Post Type
			 */
			$args['post_type'] = $this->getGroupControlQueryPostType();

			/**
			 * Set Exclude Post
			 */
			$exclude_by   = $this->getGroupControlQueryParamBy('exclude');
			$current_post = [];

			if (in_array('current_post', $exclude_by) && is_singular()) {
				$current_post = [get_the_ID()];
			}

			if (in_array('manual_selection', $exclude_by)) {
				$exclude_ids          = $settings['posts_exclude_ids'];
				$args['post__not_in'] = array_merge($current_post, wp_parse_id_list($exclude_ids));
			}

			/**
			 * Set Authors
			 */
			$include_by    = $this->getGroupControlQueryParamBy('include');
			$exclude_by    = $this->getGroupControlQueryParamBy('exclude');
			$include_users = [];
			$exclude_users = [];

			if (in_array('authors', $include_by)) {
				$include_users = wp_parse_id_list($settings['posts_include_author_ids']);
			}

			if (in_array('authors', $exclude_by)) {
				$exclude_users = wp_parse_id_list($settings['posts_exclude_author_ids']);
				$include_users = array_diff($include_users, $exclude_users);
			}

			if (!empty($include_users)) {
				$args['author__in'] = $include_users;
			}

			if (!empty($exclude_users)) {
				$args['author__not_in'] = $exclude_users;
			}

			/**
			 * Set Taxonomy
			 */
			$include_by    = $this->getGroupControlQueryParamBy('include');
			$exclude_by    = $this->getGroupControlQueryParamBy('exclude');
			$include_terms = [];
			$exclude_terms = [];
			$terms_query   = [];

			if (in_array('terms', $include_by)) {
				$include_terms = wp_parse_id_list($settings['posts_include_term_ids']);
			}

			if (in_array('terms', $exclude_by)) {
				$exclude_terms = wp_parse_id_list($settings['posts_exclude_term_ids']);
				$include_terms = array_diff($include_terms, $exclude_terms);
			}

			if (!empty($include_terms)) {
				$tax_terms_map = $this->mapGroupControlQuery($include_terms);

				foreach ($tax_terms_map as $tax => $terms) {
					$terms_query[] = [
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => 'IN',
					];
				}
			}

			if (!empty($exclude_terms)) {
				$tax_terms_map = $this->mapGroupControlQuery($exclude_terms);

				foreach ($tax_terms_map as $tax => $terms) {
					$terms_query[] = [
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => 'NOT IN',
					];
				}
			}

			if (!empty($terms_query)) {
				$args['tax_query']             = $terms_query;
				$args['tax_query']['relation'] = 'AND';
			}
		}

		if ($this->get_settings_for_display('query_id')) {
			add_action('pre_get_posts', [$this, 'pre_get_posts_query_filter']);
		}

		// fixing custom offset
		## https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
		add_action('pre_get_posts', [$this, 'fix_query_offset'], 1);
		add_filter('found_posts', [$this, 'prefix_adjust_offset_pagination'], 1, 2);

		return $args;
	}

	/**
	 * @return mixed
	 */
	private function getGroupControlQueryPostType() {
		return $this->get_settings_for_display('posts_source');
	}

	/**
	 * Get Query Params by args
	 *
	 * @param string $by
	 *
	 * @return array|mixed
	 */
	private function getGroupControlQueryParamBy($by = 'exclude') {
		$mapBy = [
			'exclude' => 'posts_exclude_by',
			'include' => 'posts_include_by',
		];

		$setting = $this->get_settings_for_display($mapBy[$by]);

		return (!empty($setting) ? $setting : []);
	}

	/**
	 * @param array $term_ids
	 *
	 * @return array
	 */
	private function mapGroupControlQuery($term_ids = []) {
		$terms = get_terms(
			[
				'term_taxonomy_id' => $term_ids,
				'hide_empty'       => false,
			]
		);

		$tax_terms_map = [];

		foreach ($terms as $term) {
			$taxonomy                   = $term->taxonomy;
			$tax_terms_map[$taxonomy][] = $term->term_id;
		}

		return $tax_terms_map;
	}

	/**
	 * @return array|string[]|\WP_Post_Type[]
	 */
	private function getGroupControlQueryPostTypes() {
		$post_types = get_post_types(['public' => true], 'objects');
		$post_types = array_column($post_types, 'label', 'name');

		$ignorePostTypes = [
			'elementor_library'    => '',
			'attachment'           => '',
			'bdt_template_manager' => '',
			'bdt-custom-template'  => '',
		];

		$post_types = array_diff_key($post_types, $ignorePostTypes);

		$extra_types = [
			'manual_selection'   => __('Manual Selection', 'pixel-gallery'),
			'current_query'      => __('Current Query', 'pixel-gallery'),
			'_related_post_type' => __('Related', 'pixel-gallery'),
		];

		$post_types = array_merge($post_types, $extra_types);

		return $post_types;
	}

	private function pg_get_supported_taxonomies() {
		$supported_taxonomies = [];

		$public_types = pixel_gallery_get_post_types();

		foreach ($public_types as $type => $title) {
			$taxonomies = get_object_taxonomies($type, 'objects');
			foreach ($taxonomies as $key => $tax) {
				if (!array_key_exists($key, $supported_taxonomies)) {
					$label = $tax->label;
					if (in_array($tax->label, $supported_taxonomies)) {
						$label = $tax->label . ' (' . $tax->name . ')';
					}
					$supported_taxonomies[$key] = $label;
				}
			}
		}

		return $supported_taxonomies;
	}

	/**
	 * @param WP_Query $query fix the offset
	 */

	function fix_query_offset(&$query) {

		if (isset($query->query_vars['offset_to_fix'])) {

			if ($query->is_paged) {
				$page_offset = $query->query_vars['offset_to_fix'] + (($query->query_vars['paged'] - 1) * $query->query_vars['posts_per_page']);
				$query->set('offset', $page_offset);
			} else {
				$query->set('offset', $query->query_vars['offset_to_fix']);
			}
		}
	}

	function prefix_adjust_offset_pagination($found_posts, $query) {

		if (isset($query->query_vars['offset_to_fix'])) {
			$offset_to_fix = intval($query->query_vars['offset_to_fix']);

			if ($offset_to_fix) {
				$found_posts -= $offset_to_fix;
			}
		}

		return $found_posts;
	}

	public function pre_get_posts_query_filter($wp_query) {

		if ($this) {
			$query_id = $this->get_settings_for_display('query_id');
			do_action("pixel_gallery/query/{$query_id}", $wp_query, $this);
		}
	}
}
