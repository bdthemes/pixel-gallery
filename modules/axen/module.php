<?php
namespace PixelGallery\Modules\Axen;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'axen';
	}

	public function get_widgets() {
		$widgets = [
			'Axen',
		];

		return $widgets;
	}
}
