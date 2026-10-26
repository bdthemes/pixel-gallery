/**
 * Hosts a PHP-rendered panel inside the app: Other Plugins, and the tabs an
 * add-on registers (Pixel Gallery Pro: Rollback, White Label, Custom CSS & JS,
 * License).
 *
 * PHP prints each panel once, hidden, under #pga-admin-panels. This page moves
 * that very node into its card while it is shown and puts it back when the
 * route changes, so the panel's own markup, inline scripts, event handlers and
 * form state survive navigation; nothing is re-rendered or re-executed.
 */
import { useLayoutEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PageHeader from '../components/shell/PageHeader';
import EmptyState from '../components/EmptyState';

/**
 * The License panel is wrapped by the dashboard itself; add-on panels are the
 * `#<id>_page` panes Pixel Gallery Pro prints on `pixel_gallery/admin/settings_pages`.
 */
function findPanel( id ) {
	const holder = document.getElementById( 'pga-admin-panels' );
	if ( ! holder ) {
		return null;
	}
	return (
		holder.querySelector( `[data-pga-panel="${ id }"]` ) ||
		holder.querySelector( `[id="${ id }_page"]` )
	);
}

/**
 * Code editors (wp.codeEditor / CodeMirror) and similar widgets measure
 * themselves when they initialise, which happened while the panel was hidden.
 */
function refreshWidgets( node ) {
	node.querySelectorAll( '.CodeMirror' ).forEach( ( el ) => {
		if ( el.CodeMirror && typeof el.CodeMirror.refresh === 'function' ) {
			el.CodeMirror.refresh();
		}
	} );
	if ( window.jQuery ) {
		window.jQuery( node ).trigger( 'shown' );
	}
	window.dispatchEvent( new window.Event( 'resize' ) );
}

export default function PanelPage( { panel } ) {
	const hostRef = useRef( null );
	const [ missing, setMissing ] = useState( false );

	useLayoutEffect( () => {
		const host = hostRef.current;
		const node = findPanel( panel.id );
		const home = node && node.parentNode;

		if ( ! host || ! node || ! home ) {
			setMissing( true );
			return undefined;
		}

		setMissing( false );
		host.appendChild( node );
		window.requestAnimationFrame( () => refreshWidgets( node ) );

		return () => {
			home.appendChild( node );
		};
	}, [ panel.id ] );

	return (
		<div>
			<PageHeader title={ panel.title } />
			<div className="pga-admin-panel-host bg-white dark:bg-ink-card border border-[#e9e9ed] dark:border-ink-border rounded-card p-6 max-[640px]:p-4 overflow-x-auto">
				{ /* The panel reset and the classic admin styles (scoped to .pixel-gallery-dashboard) apply to a moved pane too. */ }
				<div ref={ hostRef } className="pga-admin-panel pixel-gallery-dashboard" />
				{ missing ? (
					<EmptyState title={ __( 'This panel could not be loaded. Reload the page and try again.', 'pixel-gallery' ) } />
				) : null }
			</div>
		</div>
	);
}
