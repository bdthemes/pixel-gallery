<?php
namespace PixelGallery\Modules\Mystic;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'mystic';
	}

	public function get_widgets() {
		$widgets = [
			'Mystic',
		];

		return $widgets;
	}
}
