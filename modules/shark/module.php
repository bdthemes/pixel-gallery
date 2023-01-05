<?php
namespace PixelGallery\Modules\Shark;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'shark';
	}

	public function get_widgets() {
		$widgets = [
			'Shark',
		];

		return $widgets;
	}
}
