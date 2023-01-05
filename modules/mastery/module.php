<?php
namespace PixelGallery\Modules\Mastery;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'mastery';
	}

	public function get_widgets() {
		$widgets = [
			'Mastery',
		];

		return $widgets;
	}
}
