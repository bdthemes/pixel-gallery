/**
 * Route table. The tab strip, the WordPress submenu sync and the legacy-hash
 * redirects are all derived from it.
 *
 * `legacy` is the hash the classic dashboard used for the same screen
 * (admin.php?page=pixel_gallery_options#pixel_gallery_active_modules); old
 * links and the WordPress submenu entries still use those.
 */
import { __ } from '@wordpress/i18n';
import config, { sectionFields } from '../utils/config';
import {
	DashboardIcon,
	GridIcon,
	PuzzleIcon,
	ServerIcon,
	PackageIcon,
	ClockIcon,
	TagIcon,
	CodeIcon,
	KeyIcon,
	RocketIcon,
	GaugeIcon,
} from '../components/ui/Icons';
import Dashboard from '../pages/Dashboard';
import SectionPage from '../pages/SectionPage';
import SystemStatusPage from '../pages/SystemStatusPage';
import PanelPage from '../pages/PanelPage';
import GetProPage from '../pages/GetProPage';
import OtherPluginsPage from '../pages/OtherPluginsPage';
import PerformancePage, { ASSET_FIELD } from '../pages/PerformancePage';
import LicensePage from '../pages/pro/LicensePage';
import WhiteLabelPage from '../pages/pro/WhiteLabelPage';
import CustomCodePage from '../pages/pro/CustomCodePage';
import RollbackPage from '../pages/pro/RollbackPage';
import ProFeaturePage from '../pages/ProFeaturePage';

/**
 * Native pages for add-on panels. When the add-on hands over the page data
 * (panel.data, from the `pixel_gallery/admin/react_pages` filter), the app
 * renders that page; otherwise the panel's PHP markup is shown.
 */
const NATIVE_PAGES = {
	pixel_gallery_license_settings: LicensePage,
	pixel_gallery_rollback_version: RollbackPage,
};

/**
 * Pixel Gallery Pro's "Extra Options" panel holds Custom CSS & JS and White
 * Label. With native data (`{ custom_code, white_label }`) the app shows them
 * as two tabs, as Element Pack and Prime Slider do, and the classic
 * #pixel_gallery_extra_options hash opens Custom Code.
 */
const EXTRA_OPTIONS = 'pixel_gallery_extra_options';

/**
 * Pro features that have their own tab once Pixel Gallery Pro is active. In
 * the free plugin the same tabs show, in the same places, with a page that
 * describes the feature and links to the upgrade (the Get Pro page's items).
 */
function proFeatureRoutes() {
	return [
		{
			legacy: 'pixel_gallery_white_label',
			path: '/white-label',
			label: __( 'White Label', 'pixel-gallery' ),
			page: {
				icon: TagIcon,
				accent: 'woo',
				title: __( 'White Label', 'pixel-gallery' ),
				subtitle: __( 'Rebrand Pixel Gallery across the admin for your clients.', 'pixel-gallery' ),
				headline: __( 'Make Pixel Gallery your own brand', 'pixel-gallery' ),
				description: __( 'Rebrand the plugin for client sites: your name, your icon and your logo, with the license screen and the plugin itself hidden from clients.', 'pixel-gallery' ),
				points: [
					{ title: __( 'Your name and menu icon', 'pixel-gallery' ), sub: __( 'Replace "Pixel Gallery" and its icon throughout the admin.', 'pixel-gallery' ) },
					{ title: __( 'Your logo', 'pixel-gallery' ), sub: __( 'Show your own logo in the dashboard header.', 'pixel-gallery' ) },
					{ title: __( 'Hide the license menu', 'pixel-gallery' ), sub: __( 'Keep license details away from clients.', 'pixel-gallery' ) },
					{ title: __( 'Hide Pixel Gallery', 'pixel-gallery' ), sub: __( 'Remove the plugin from the admin, with an access link sent to your email.', 'pixel-gallery' ) },
				],
			},
		},
		{
			legacy: EXTRA_OPTIONS,
			path: '/custom-code',
			label: __( 'Custom Code', 'pixel-gallery' ),
			page: {
				icon: CodeIcon,
				accent: 'violet',
				title: __( 'Custom CSS & JS', 'pixel-gallery' ),
				subtitle: __( 'Inject custom CSS/JS into your site and exclude pages from loading it.', 'pixel-gallery' ),
				headline: __( 'Add site-wide CSS and JavaScript', 'pixel-gallery' ),
				description: __( 'Write CSS and JavaScript once and load it across the site, in the header or the footer, and keep it off the pages where you don\'t want it.', 'pixel-gallery' ),
				points: [
					{ title: __( 'Header code', 'pixel-gallery' ), sub: __( 'CSS and JavaScript printed in the <head> of every page.', 'pixel-gallery' ) },
					{ title: __( 'Footer code', 'pixel-gallery' ), sub: __( 'CSS and JavaScript printed before the closing </body> tag.', 'pixel-gallery' ) },
					{ title: __( 'Code editor', 'pixel-gallery' ), sub: __( 'Syntax highlighting, line numbers and bracket matching.', 'pixel-gallery' ) },
					{ title: __( 'Page exclusions', 'pixel-gallery' ), sub: __( 'Choose pages and posts that should not load the custom code.', 'pixel-gallery' ) },
				],
			},
		},
		{
			legacy: 'pixel_gallery_rollback_version',
			path: '/rollback',
			slot: 'more',
			label: __( 'Rollback Version', 'pixel-gallery' ),
			page: {
				icon: ClockIcon,
				accent: 'green',
				title: __( 'Rollback Version', 'pixel-gallery' ),
				subtitle: __( 'Restore a previous version and get priority help.', 'pixel-gallery' ),
				headline: __( 'Roll back an update in one click', 'pixel-gallery' ),
				description: __( 'If an update causes a problem on your site, go back to the version you had before while it is being fixed, and get priority help from the BdThemes team.', 'pixel-gallery' ),
				points: [
					{ title: __( 'One-click rollback', 'pixel-gallery' ), sub: __( 'Restore the previous version straight from the dashboard.', 'pixel-gallery' ) },
					{ title: __( 'Automatic backup', 'pixel-gallery' ), sub: __( 'The installed version is backed up before each update.', 'pixel-gallery' ) },
					{ title: __( 'Premium support', 'pixel-gallery' ), sub: __( 'Priority help from the BdThemes support team.', 'pixel-gallery' ) },
					{ title: __( 'Pro updates', 'pixel-gallery' ), sub: __( 'New Pro widgets and features as they are released.', 'pixel-gallery' ) },
				],
			},
		},
	];
}

/** Known PHP panels: route path, icon and where they sit in the strip. */
const PANEL_ROUTES = {
	pixel_gallery_rollback_version: { path: '/rollback', icon: ClockIcon, slot: 'more' },
	[ EXTRA_OPTIONS ]: { path: '/extra-options', icon: CodeIcon, slot: 'primary' },
	pixel_gallery_license_settings: { path: '/license', icon: KeyIcon, slot: 'primary' },
};

/** Custom Code and White Label tabs from the Extra Options panel's native data. */
function extraOptionsRoutes( panel ) {
	return [
		{
			path: '/custom-code',
			legacy: EXTRA_OPTIONS,
			label: __( 'Custom Code', 'pixel-gallery' ),
			icon: CodeIcon,
			slot: 'primary',
			end: true,
			render: () => <CustomCodePage panel={ panel } data={ panel.data.custom_code } />,
		},
		{
			path: '/white-label',
			legacy: 'pixel_gallery_white_label',
			label: __( 'White Label', 'pixel-gallery' ),
			icon: TagIcon,
			slot: 'primary',
			end: true,
			render: () => <WhiteLabelPage panel={ panel } data={ panel.data.white_label } />,
		},
	];
}

function sectionRoute( path, legacy, section, label, icon, title, subtitle ) {
	return {
		path,
		legacy,
		label,
		icon,
		slot: 'primary',
		end: true,
		render: () => (
			<SectionPage
				section={ section }
				title={ title || label }
				subtitle={ subtitle }
			/>
		),
	};
}

export function buildRoutes() {
	const list = [
		{
			path: '/',
			legacy: 'pixel_gallery_welcome',
			label: __( 'Dashboard', 'pixel-gallery' ),
			icon: DashboardIcon,
			slot: 'primary',
			end: true,
			render: () => <Dashboard />,
		},
		{
			path: '/widgets',
			legacy: 'pixel_gallery_active_modules',
			label: __( 'Widgets', 'pixel-gallery' ),
			icon: GridIcon,
			slot: 'primary',
			end: true,
			render: () => (
				<SectionPage
					section="pixel_gallery_active_modules"
					title={ __( 'Widgets', 'pixel-gallery' ) }
				/>
			),
		},
	];

	if ( sectionFields( 'pixel_gallery_elementor_extend' ).length ) {
		list.push(
			sectionRoute(
				'/extensions',
				'pixel_gallery_elementor_extend',
				'pixel_gallery_elementor_extend',
				__( 'Extensions', 'pixel-gallery' ),
				PuzzleIcon
			)
		);
	}

	if ( sectionFields( 'pixel_gallery_other_settings' ).some( ( f ) => f.name === ASSET_FIELD ) ) {
		list.push( {
			path: '/performance',
			legacy: 'pixel_gallery_other_settings',
			label: __( 'Performance', 'pixel-gallery' ),
			icon: GaugeIcon,
			// Right after Extensions in the tab strip, as in Element Pack.
			slot: 'primary',
			end: true,
			render: () => <PerformancePage />,
		} );
	}

	list.push(
		{
			path: '/system-status',
			legacy: 'pixel_gallery_analytics_system_req',
			label: __( 'System Status', 'pixel-gallery' ),
			icon: ServerIcon,
			slot: 'more',
			end: true,
			render: () => <SystemStatusPage />,
		}
	);

	list.push( {
		path: '/other-plugins',
		legacy: 'pixel_gallery_other_plugins',
		label: __( 'Other Plugins', 'pixel-gallery' ),
		icon: PackageIcon,
		slot: 'more',
		end: true,
		render: () => <OtherPluginsPage />,
	} );

	( config.panels || [] ).forEach( ( panel ) => {
		if ( panel.id === EXTRA_OPTIONS && panel.data && panel.data.custom_code && panel.data.white_label ) {
			list.push( ...extraOptionsRoutes( panel ) );
			return;
		}
		const known = PANEL_ROUTES[ panel.id ] || {
			path: `/${ panel.id.replace( /^pixel_gallery_/, '' ).replace( /_/g, '-' ) }`,
			icon: PuzzleIcon,
			slot: 'more',
		};
		list.push( {
			path: known.path,
			legacy: panel.id,
			// A tab label of our own where the add-on's panel title is longer.
			label: known.label || panel.title,
			icon: known.icon,
			slot: known.slot,
			end: true,
			render: () => {
				const Native = panel.data ? NATIVE_PAGES[ panel.id ] : null;
				return Native ? <Native panel={ panel } data={ panel.data } /> : <PanelPage panel={ panel } />;
			},
		} );
	} );

	if ( ! config.isPro ) {
		// The Pro features' tabs, where Pro puts them (an add-on panel that is
		// registered anyway keeps its own page).
		proFeatureRoutes().forEach( ( feature ) => {
			if ( list.some( ( route ) => route.path === feature.path || route.legacy === feature.legacy ) ) {
				return;
			}
			list.push( {
				path: feature.path,
				legacy: feature.legacy,
				label: feature.label,
				icon: feature.page.icon,
				slot: feature.slot || 'primary',
				end: true,
				render: () => <ProFeaturePage { ...feature.page } />,
			} );
		} );

		list.push( {
			path: '/get-pro',
			legacy: 'pixel_gallery_get_pro',
			label: __( 'You May Also Need', 'pixel-gallery' ),
			icon: RocketIcon,
			// A tab of its own in the strip, after the Pro features' tabs.
			slot: 'primary',
			end: true,
			render: () => <GetProPage />,
		} );
	}

	return list;
}

export const routes = buildRoutes();

/** Route for a classic-dashboard hash such as `pixel_gallery_active_modules`. */
export function routeForLegacyHash( hash ) {
	const id = String( hash || '' ).replace( /^#/, '' );
	const match = routes.find( ( route ) => route.legacy === id );
	if ( match ) {
		return match.path;
	}
	// Without Pro, the classic dashboard's last tab (#pixel_gallery_license_settings) was "Get Pro".
	if ( id === 'pixel_gallery_license_settings' && ! config.isPro ) {
		return '/get-pro';
	}
	return null;
}
