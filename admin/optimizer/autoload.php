<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Minimal, explicit autoloader for the bundled matthiasmullie/minify library.
 *
 * Paths are resolved from __DIR__ so the includes never depend on the current
 * working directory or the PHP include_path.
 */
$bdtpg_minify_path = __DIR__ . '/vendor/matthiasmullie';

require_once $bdtpg_minify_path . '/path-converter/src/ConverterInterface.php';
require_once $bdtpg_minify_path . '/path-converter/src/Converter.php';
require_once $bdtpg_minify_path . '/path-converter/src/NoConverter.php';
require_once $bdtpg_minify_path . '/minify/src/Exception.php';
require_once $bdtpg_minify_path . '/minify/src/Exceptions/BasicException.php';
require_once $bdtpg_minify_path . '/minify/src/Exceptions/FileImportException.php';
require_once $bdtpg_minify_path . '/minify/src/Exceptions/IOException.php';
require_once $bdtpg_minify_path . '/minify/src/Exceptions/PatternMatchException.php';
require_once $bdtpg_minify_path . '/minify/src/Minify.php';
require_once $bdtpg_minify_path . '/minify/src/CSS.php';
require_once $bdtpg_minify_path . '/minify/src/JS.php';

unset( $bdtpg_minify_path );
