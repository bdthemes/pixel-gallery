<?php
namespace PixelGallery\Modules\Panda;

use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'panda';
	}

	public function get_widgets() {
		$widgets = [
			'Panda',
		];

		return $widgets;
	}
}
