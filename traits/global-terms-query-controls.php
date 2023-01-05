<?php

namespace PixelGallery\Traits;

use Elementor\Controls_Manager;

defined('ABSPATH') || die();

trait Global_Terms_Query_Controls {
    protected function render_terms_query_controls($taxonomy = 'category') {

        $this->start_controls_section(
            'section_term_query',
            [
                'label' => __('Query', 'pixel-gallery'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'display_category',
            [
                'label' => __('Type', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all' => __('All', 'pixel-gallery'),
                    'parents' => __('Only Parents', 'pixel-gallery'),
                    'child' => __('Only Child', 'pixel-gallery')
                ],
            ]
        );

        $this->add_control(
        	'limit',
        	[
        		'label' => esc_html__('Item Limit', 'pixel-gallery'),
        		'type'  => Controls_Manager::SLIDER,
        		'range' => [
        			'px' => [
        				'min' => 1,
        				'max' => 20,
        			],
        		],
        		'default' => [
        			'size' => 6,
        		],
        	]
        );

        $this->start_controls_tabs(
            'tabs_terms_include_exclude',
            [
                'condition' => ['display_category' => 'all']
            ]
        );
        $this->start_controls_tab(
            'tab_term_include',
            [
                'label' => __('Include', 'pixel-gallery'),
                'condition' => ['display_category' => 'all']
            ]
        );

        $this->add_control(
            'cats_include_by_id',
            [
                'label' => __('Categories', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'condition' => [
                    'display_category' => 'all'
                ],
                'options' => pixel_gallery_get_terms($taxonomy),
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_term_exclude',
            [
                'label' => __('Exclude', 'pixel-gallery'),
                'condition' => ['display_category' => 'all']
            ]
        );

        $this->add_control(
            'cats_exclude_by_id',
            [
                'label' => __('Categories', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'condition' => [
                    'display_category' => 'all'
                ],
                'options' => pixel_gallery_get_terms($taxonomy),
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'child_cats_notice',
            [
                'type'              => Controls_Manager::RAW_HTML,
                'raw'               => __('WARNING!, Must Select Parent Category from Child Categories of.', 'pixel-gallery'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                'condition' => [
                    'display_category' => 'child',
                    'parent_cats' => 'none'
                ],
            ],
        );
        $this->add_control(
            'parent_cats',
            [
                'label' => __('Child Categories of', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => pixel_gallery_get_only_parent_cats($taxonomy),
                'condition' => [
                    'display_category' => 'child'
                ],
            ]
        );


        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'       => esc_html__('Name', 'pixel-gallery'),
                    'count'  => esc_html__('Count', 'pixel-gallery'),
                    'slug' => esc_html__('Slug', 'pixel-gallery'),
                    // 'menu_order' => esc_html__('Menu Order', 'pixel-gallery'),
                    // 'rand'       => esc_html__('Random', 'pixel-gallery'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'pixel-gallery'),
                'type' => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'desc' => __('Descending', 'pixel-gallery'),
                    'asc' => __('Ascending', 'pixel-gallery'),
                ],
            ]
        );
        $this->add_control(
            'hide_empty',
            [
                'label'         => __('Hide Empty', 'pixel-gallery'),
                'type'          => Controls_Manager::SWITCHER,
            ]
        );

        $this->end_controls_section();
    }
}
