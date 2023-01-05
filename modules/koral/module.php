<?php
namespace PixelGallery\Modules\Koral;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'koral';
	}

	public function get_widgets() {
		$widgets = [
			'Koral',
		];

		return $widgets;
	}
}
