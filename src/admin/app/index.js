/**
 * Pixel Gallery admin dashboard entry point.
 *
 * Mounts the React root into the PHP-rendered #pga-admin-app node.
 */
import { createRoot } from '@wordpress/element';
import App from './app';
import './styles/index.scss';

const node = document.getElementById( 'pga-admin-app' );

if ( node ) {
	createRoot( node ).render( <App /> );
}
