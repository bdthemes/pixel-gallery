<?php

if ( ! defined( 'BDTPG_TITLE' ) ) {
    $white_label_title = get_option( 'pg_white_label_title' );
	define( 'BDTPG_TITLE', $white_label_title );
}

if ( ! defined( 'BDTPG_LO' ) ) {
    $hide_license = get_option( 'pg_white_label_hide_license', false );
    if ( $hide_license ) {
        define( 'BDTPG_LO', true );
    }
}

if ( ! defined( 'BDTPG_HIDE' ) ) {
    $hide_pg = get_option( 'pg_white_label_bdtpg_hide', false );
    if ( $hide_pg ) {
        define( 'BDTPG_HIDE', true );
    }
}