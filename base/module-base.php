<?php

namespace PixelGallery\Base;

use Elementor\Widget_Base;
use PixelGallery\Pixel_Gallery_Loader;

if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}

abstract class Module_Base extends Widget_Base {

    protected function pg_is_edit_mode() {

        if ( Pixel_Gallery_Loader::elementor()->preview->is_preview_mode() || Pixel_Gallery_Loader::elementor()->editor->is_edit_mode() ) {
            return true;
        }

        return false;
    }
}

