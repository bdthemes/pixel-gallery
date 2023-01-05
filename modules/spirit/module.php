<?php
namespace PixelGallery\Modules\Spirit;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'spirit';
	}

	public function get_widgets() {
		$widgets = [
			'Spirit',
		];

		return $widgets;
	}
}
