<?php
namespace PixelGallery\Modules\Wisdom;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'wisdom';
	}

	public function get_widgets() {
		$widgets = [
			'Wisdom',
		];

		return $widgets;
	}
}
