<?php
namespace PixelGallery\Modules\Ranch;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'ranch';
	}

	public function get_widgets() {
		$widgets = [
			'Ranch',
		];

		return $widgets;
	}
}
