<?php

namespace PixelGallery\Includes\Controls\SelectInput;

use Exception;
use WP_Query;
use PixelGallery\Pixel_Gallery_Loader;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Dynamic_Select_Input_Module {

	const ACTION = '';

	private static $instance = null;

	/**
	 * Returns the instance.
	 *
	 * @return object
	 * @since  1.0.0
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Init method
	 */

	/**
	 * Constructor.
	 */
	public function init() {
		add_action('wp_ajax_pixel_gallery_dynamic_select_input_data', array($this, 'getSelectInputData'));
	}

	/**
	 * get Ajax Data
	 */
	public function getSelectInputData() {
        $nonce = isset($_POST['security']) ? sanitize_text_field(wp_unslash($_POST['security'])) : '';

		try {
			if (!wp_verify_nonce($nonce, 'pixel_gallery_dynamic_select')) {
				throw new Exception('Invalid request');
			}

			if (!current_user_can('edit_posts')) {
				throw new Exception('Unauthorized request');
			}

            $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';

			if ($query == 'terms') {
				$data = $this->getTerms();
			} else if ($query == 'authors') {
				$data = $this->getAuthors();
			} else if ($query == 'authors_role') {
				$data = $this->getAuthorRoles();
			} else if ($query == 'only_post') {
				$data = $this->getOnlyPosts();
			} else if ($query == 'elementor_template') {
				$data = $this->getElementorTemplates();
			} else if ($query == 'anywhere_template') {
				$data = $this->getAnywhereTemplates();
			} elseif ($query == 'elementor_dynamic_loop_template') {
				$data = $this->getDynamicTemplates();
			} else {
				$data = $this->getPosts();
			}

			wp_send_json_success($data);
		} catch (Exception $e) {
			wp_send_json_error($e->getMessage());
		}

		die();
	}

	/**
	 * Get Post Type
	 *
	 * The post type comes from the request. Anything but the gallery query's own
	 * post types (see pixel_gallery_query_post_types()) is ignored, so the endpoint
	 * cannot be pointed at WooCommerce coupons, form entries or other internal types.
	 *
	 * @return string
	 */
	protected function getPostType() {
		$post_type = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via wp_verify_nonce() in getSelectInputData().

		if ('' === $post_type) {
			return '';
		}

		// '_related_post_type' is an internal marker handled by the callers.
		if ('_related_post_type' === $post_type) {
			return $post_type;
		}

		return in_array($post_type, $this->getAllPublicPostTypes(), true) ? $post_type : '';
	}

	/**
	 * @return string[]|\WP_Post_Type[]
	 */
	protected function getAllPublicPostTypes() {
		return pixel_gallery_query_post_types();
	}

	/**
	 * @return string
	 */
	protected function getSearchQuery() {
		return isset($_POST['search_text']) ? sanitize_text_field(wp_unslash($_POST['search_text'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via wp_verify_nonce() in getSelectInputData().
	}

	/**
	 * Add a search term to a post query.
	 *
	 * This endpoint only needs edit_posts, which a Contributor has. WordPress
	 * searches content and excerpts too, so a term would match inside other
	 * authors' password-protected posts and reveal, title by title, what they
	 * say. Users who may not edit others' posts search titles only.
	 *
	 * @param array  $args       WP_Query arguments.
	 * @param string $searchText Search term.
	 * @return array
	 */
	protected function addSearchArgs(array $args, $searchText) {
		if ('' === $searchText) {
			return $args;
		}

		$args['s'] = $searchText;

		if (!current_user_can('edit_others_posts')) {
			$args['search_columns'] = ['post_title'];
		}

		return $args;
	}

	/**
	 * Build the select options for a post query.
	 *
	 * Queries here are published-only, which is all a gallery shows; anything the
	 * current user may not read is still skipped. Titles are sent as plain text:
	 * the control escapes them when it prints the options.
	 *
	 * @param WP_Query $query
	 * @return array
	 */
	protected function getPostResults(WP_Query $query) {
		$results = [];

		foreach ($query->posts as $post) {
			if (!current_user_can('read_post', $post->ID)) {
				continue;
			}

			$post_type_obj = get_post_type_object($post->post_type);
			$text          = ($post_type_obj && $post_type_obj->hierarchical) ? $this->get_post_name_with_parents($post) : $post->post_title;

			$results[] = [
				'id'   => $post->ID,
				'text' => $this->plainText($text),
			];
		}

		return $results;
	}

	/**
	 * Option label as plain text (the control escapes it when it is displayed).
	 *
	 * @param string $text
	 * @return string
	 */
	protected function plainText($text) {
		return html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES, get_bloginfo('charset'));
	}

	/**
	 * @return int[]
	 */
	protected function getselecedIds() {
		if (!isset($_POST['ids'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via wp_verify_nonce() in getSelectInputData().
			return [];
		}

		// Every consumer feeds this into post__in / include, so force a flat list of
		// positive integers instead of trusting the raw request shape.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified via wp_verify_nonce() in getSelectInputData(); wp_parse_id_list() sanitizes.
		$ids = wp_unslash($_POST['ids']);

		return array_values(array_filter(wp_parse_id_list(is_array($ids) ? array_filter($ids, 'is_scalar') : $ids)));
	}


	/**
	 * @param string $taxonomy
	 *
	 * @return mixed|string
	 */
	public function getTaxonomyName($taxonomy = '') {
		$taxonomies = get_taxonomies(['public' => true], 'objects');
		$taxonomies = array_column($taxonomies, 'label', 'name');

		return isset($taxonomies[$taxonomy]) ? $taxonomies[$taxonomy] : '';
	}

	/**
	 * @return string[]|\WP_Taxonomy[]
	 */
	protected function getAllPublicTaxonomies() {
		return array_values(get_taxonomies(['public' => true]));
	}

	/**
	 * Get Post Query Data
	 *
	 * @return array
	 */
	public function getPosts() {
		$include    = $this->getselecedIds();
		$searchText = $this->getSearchQuery();

		$args = [
			'post_status' => 'publish',
		];

		if ($this->getPostType() && $this->getPostType() !== '_related_post_type') {
			$args['post_type'] = $this->getPostType();
		} else {
			$args['post_type'] = $this->getAllPublicPostTypes();
		}

		if (!empty($include)) {
			$args['post__in']       = $include;
			$args['posts_per_page'] = count($include);
		} else {
			$args['posts_per_page'] = 20;
		}

		$args = $this->addSearchArgs($args, $searchText);

		return $this->getPostResults(new WP_Query($args));
	}

	public function getDynamicTemplates() {
		$searchText = $this->getSearchQuery();

		// Same rule as Elementor's own template list: published templates only.
		$args = [
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
		];

		$args = $this->addSearchArgs($args, $searchText);

		return $this->getPostResults(new WP_Query($args));
	}

	public function getOnlyPosts() {
		$include    = $this->getselecedIds();
		$searchText = $this->getSearchQuery();

		$args = [
			'post_type'   => 'post',
			'post_status' => 'publish',
		];

		if (!empty($include)) {
			$args['post__in']       = $include;
			$args['posts_per_page'] = count($include);
		} else {
			$args['posts_per_page'] = 20;
		}

		$args = $this->addSearchArgs($args, $searchText);

		return $this->getPostResults(new WP_Query($args));
	}

	private function get_post_name_with_parents($post, $max = 3) {
		if (0 === $post->post_parent) {
			return $post->post_title;
		}
		$separator = is_rtl() ? ' < ' : ' > ';
		$test_post = $post;
		$names     = [];
		while ($test_post->post_parent > 0) {
			$test_post = get_post($test_post->post_parent);
			if (!$test_post) {
				break;
			}
			$names[] = $test_post->post_title;
		}

		$names = array_reverse($names);
		if (count($names) < ($max)) {
			return implode($separator, $names) . $separator . $post->post_title;
		}

		$name_string = '';
		for ($i = 0; $i < ($max - 1); $i++) {
			$name_string .= $names[$i] . $separator;
		}

		return $name_string . '...' . $separator . $post->post_title;
	}

	/**
	 * Get Terms query data
	 *
	 * @return array
	 */
	public function getTerms() {
		$search_text = $this->getSearchQuery();
		$taxonomies  = $this->getAllPublicTaxonomies();
		$include     = $this->getselecedIds();

		$post_type = 'any';

		if ($this->getPostType() && $this->getPostType() !== '_related_post_type') {
			$post_type = $this->getPostType();
		}
		$post_taxonomies = get_object_taxonomies($post_type);
		$taxonomies      = array_intersect($post_taxonomies, $taxonomies);
		$data            = [];

		if (empty($taxonomies)) {
			return $data;
		}

		$args = [
			'taxonomy'   => $taxonomies,
			'hide_empty' => true,
		];

		if (!empty($include)) {
			$args['include'] = $include;
		}

		if ($search_text) {
			$args['number'] = 20;
			$args['search'] = $search_text;
		}
		$terms = get_terms($args);

		if (is_wp_error($terms) || empty($terms)) {
			return $data;
		}

		foreach ($terms as $term) {
			$label         = $term->name;
			$taxonomy_name = $this->getTaxonomyName($term->taxonomy);

			if ($taxonomy_name) {
				$label = "{$taxonomy_name}: {$label}";
			}

			$data[] = [
				'id'   => $term->term_taxonomy_id,
				'text' => $this->plainText($label),
			];
		}

		return $data;
	}

	/**
	 * Get Authors query Data
	 *
	 * @return array
	 */
	public function getAuthors() {
		$include     = $this->getselecedIds();
		$search_text = $this->getSearchQuery();

		$args = [
			'fields'  => ['ID', 'display_name'],
			'orderby' => 'display_name',
			// Always bounded: without a limit an empty search returns every user.
			'number'  => 20,
		];

		// Match the REST API: without list_users only people who have published
		// something are listed, and the search matches names only, never e-mail
		// addresses, URLs or login names, so it can't be used to discover them.
		$can_list_users = current_user_can('list_users');

		if (!$can_list_users) {
			$args['has_published_posts'] = true;
		}

		if (!empty($include)) {
			$args['include'] = $include;
			// Room for every already-selected author, still bounded.
			$args['number'] = min(100, max(20, count($include)));
		}

		if ($search_text) {
			$args['search'] = "*$search_text*";

			if (!$can_list_users) {
				$args['search_columns'] = ['user_nicename', 'display_name'];
			}
		}

		$users = get_users($args);

		$data = [];

		if (empty($users)) {
			return $data;
		}

		foreach ($users as $user) {
			$data[] = [
				'id'   => $user->ID,
				'text' => $this->plainText($user->display_name),
			];
		}

		return $data;
	}

	/**
	 * Get Authors Roles query Data
	 *
	 * @return array
	 */
	public function getAuthorRoles() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;
		$roles     = [];
		foreach ($all_roles as $key => $role) {
			$roles[] = [
				'id'   => $key,
				'text' => $role['name'],
			];
		}

		return $roles;
	}
	/**
	 * @return array of elementor template
	 */
	public function getElementorTemplates() {
		$searchText = $this->getSearchQuery();
		$args       = [];
		if ($searchText) {
			$args['s'] = $searchText;
		}
		$templates = Pixel_Gallery_Loader::elementor()->templates_manager->get_source('local')->get_items($args);
		$results     = [];

		if (empty($templates)) {
			$results = ['0' => __('Template Not Found!', 'pixel-gallery')];
		} else {
			foreach ($templates as $template) {
				$results[] = [
					'id'   => $template['template_id'],
					'text' => $this->plainText($template['title']),
				];
			}
		}
		return $results;
	}

	/**
	 * @return array of anywhere template
	 */
	/**
	 * @return array of anywhere templates
	 */
	public function getAnywhereTemplates() {
		$search_text = $this->getSearchQuery();
		$results = [];
		if (post_type_exists('ae_global_templates')) {
			$anywhere = get_posts(array(
				'fields'         => 'ids', // Only get post IDs
				'posts_per_page' => -1,
				'post_type'      => 'ae_global_templates',
				's' 			 => $search_text
			));
			foreach ($anywhere as $key => $value) {
				$results[] = [
					'id'   => $value,
					'text' => $this->plainText(get_the_title($value)),
				];
			}
		} else {
			$results = ['0' => esc_html__('AE Plugin Not Installed', 'pixel-gallery')];
		}

		return $results;
	}
}

function pixel_gallery_dynamic_select_input_module() {
	return Dynamic_Select_Input_Module::get_instance();
}

pixel_gallery_dynamic_select_input_module()->init();
