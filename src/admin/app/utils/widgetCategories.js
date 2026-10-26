/**
 * Widget categories from the `content_type` tokens of each field (the same
 * tokens the classic dashboard filtered by). A widget's category is its first
 * matching token; `new` is a badge, not a category.
 */
import { __ } from '@wordpress/i18n';

export const CATEGORIES = {
	custom: { label: __( 'Gallery', 'pixel-gallery' ), accent: 'blue' },
	others: { label: __( 'Others', 'pixel-gallery' ), accent: 'rose' },
};

export function tokens( mod ) {
	return String( mod.content_type || '' ).toLowerCase().split( /\s+/ ).filter( Boolean );
}

export function isNew( mod ) {
	return tokens( mod ).includes( 'new' );
}

export function categoryKeyForModule( mod ) {
	return tokens( mod ).find( ( token ) => CATEGORIES[ token ] ) || 'others';
}

export function categoryFor( mod ) {
	return CATEGORIES[ categoryKeyForModule( mod ) ];
}
