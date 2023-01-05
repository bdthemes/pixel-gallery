<?php
namespace PixelGallery\Modules\Plumb;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'plumb';
	}

	public function get_widgets() {
		$widgets = [
			'Plumb',
		];

		return $widgets;
	}
}
