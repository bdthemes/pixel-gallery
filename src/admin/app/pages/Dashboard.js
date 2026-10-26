/**
 * Dashboard overview in the Element Pack layout: welcome header, stat row,
 * health strip, quick actions, most-used widgets and release timeline on the
 * left; promo, system status and resources on the right. Every value comes
 * from the bootstrap data PHP prints with the page.
 */
import { __, sprintf } from '@wordpress/i18n';
import DashboardStatCard from '../components/dashboard/DashboardStatCard';
import HealthStrip from '../components/dashboard/HealthStrip';
import QuickActions from '../components/dashboard/QuickActions';
import SystemStatusCard from '../components/dashboard/SystemStatusCard';
import ResourcesCard from '../components/dashboard/ResourcesCard';
import ActivityCard from '../components/dashboard/ActivityCard';
import PromoCard from '../components/dashboard/PromoCard';
import MostUsedCard from '../components/dashboard/MostUsedCard';
import SuggestionsCard from '../components/dashboard/SuggestionsCard';
import { GridIcon, PuzzleIcon, ZapIcon, GaugeIcon, ServerIcon, PackageIcon, RocketIcon, PlusIcon, TagIcon, CodeIcon, KeyIcon, SparklesIcon } from '../components/ui/Icons';
import config, { sectionFields } from '../utils/config';

function percent( part, total ) {
	return total ? Math.round( ( part / total ) * 100 ) : 0;
}

function findPanel( id ) {
	return ( config.panels || [] ).find( ( panel ) => panel.id === id ) || null;
}

function hasPanel( id ) {
	return !! findPanel( id );
}

export default function Dashboard() {
	const widgets = sectionFields( 'pixel_gallery_active_modules' );
	const extensions = sectionFields( 'pixel_gallery_elementor_extend' );

	// Same rule as the Widgets page: locked Pro widgets cannot be switched,
	// so they are not counted.
	const available = widgets.filter( ( w ) => ! w.is_locked && w.plugin_active !== false );
	const extensionsOn = extensions.filter( ( w ) => w.enabled && ! w.is_locked ).length;
	const enabled = available.filter( ( w ) => w.enabled ).length;
	const used = widgets.filter( ( w ) => w.usage_count > 0 );
	const proWidgets = widgets.filter( ( w ) => w.widget_type === 'pro' );
	const extraOptions = findPanel( 'pixel_gallery_extra_options' );
	const tracking = widgets.some( ( w ) => w.has_usage );
	const checks = Array.isArray( config.system ) ? config.system : [];
	const checksOk = checks.filter( ( c ) => c.pass ).length;

	// The comment sits right before __() so it survives minifying and reaches the POT.
	const ofTotal = /* translators: 1: count, 2: total */ __( '%1$d of %2$d', 'pixel-gallery' );

	const welcome = config.userName
		? sprintf(
				/* translators: %s: user display name */
				__( 'Welcome back, %s', 'pixel-gallery' ),
				config.userName
		  )
		: __( 'Welcome back', 'pixel-gallery' );

	const metrics = [
		{
			label: __( 'Widgets enabled', 'pixel-gallery' ),
			value: `${ percent( enabled, available.length ) }%`,
			percent: percent( enabled, available.length ),
			sub: sprintf( ofTotal, enabled, available.length ),
			icon: ZapIcon,
			accent: 'rose',
			barColor: '#09d3c9',
		},
		tracking && {
			label: __( 'Widgets in use', 'pixel-gallery' ),
			value: `${ percent( used.length, widgets.length ) }%`,
			percent: percent( used.length, widgets.length ),
			sub: sprintf( ofTotal, used.length, widgets.length ),
			icon: GaugeIcon,
			accent: 'green',
			barColor: '#22a550',
		},
		extensions.length > 0 && {
			label: __( 'Extensions active', 'pixel-gallery' ),
			value: `${ percent( extensionsOn, extensions.length ) }%`,
			percent: percent( extensionsOn, extensions.length ),
			sub: sprintf( ofTotal, extensionsOn, extensions.length ),
			icon: PuzzleIcon,
			accent: 'amber',
			barColor: '#f59e0b',
		},
		{
			label: __( 'System checks passed', 'pixel-gallery' ),
			value: `${ percent( checksOk, checks.length ) }%`,
			percent: percent( checksOk, checks.length ),
			sub: sprintf( ofTotal, checksOk, checks.length ),
			icon: ServerIcon,
			accent: 'blue',
			barColor: '#408efa',
		},
	].filter( Boolean );

	const actions = [
		{ to: '/widgets', icon: GridIcon, accent: 'rose', label: __( 'Core widgets', 'pixel-gallery' ), sub: sprintf( /* translators: %d: widget count */ __( 'Toggle %d widgets', 'pixel-gallery' ), widgets.length ) },
		extensions.length > 0 && { to: '/extensions', icon: PuzzleIcon, accent: 'woo', label: __( 'Extensions', 'pixel-gallery' ), sub: __( 'Animations & more', 'pixel-gallery' ) },
		// Pro's Extra Options panel is two tabs when it hands over native data.
		extraOptions && { to: extraOptions.data ? '/custom-code' : '/extra-options', icon: CodeIcon, accent: 'violet', label: __( 'Custom code', 'pixel-gallery' ), sub: __( 'Inject CSS & JS', 'pixel-gallery' ) },
		extraOptions && extraOptions.data && { to: '/white-label', icon: TagIcon, accent: 'teal', label: __( 'White label', 'pixel-gallery' ), sub: __( 'Rebrand the plugin', 'pixel-gallery' ) },
		hasPanel( 'pixel_gallery_license_settings' ) && { to: '/license', icon: KeyIcon, accent: 'green', label: __( 'License', 'pixel-gallery' ), sub: __( 'Manage activation', 'pixel-gallery' ) },
		{ to: '/system-status', icon: ServerIcon, accent: 'blue', label: __( 'System status', 'pixel-gallery' ), sub: __( 'Server checks', 'pixel-gallery' ) },
		{ to: '/other-plugins', icon: PackageIcon, accent: 'sky', label: __( 'Other plugins', 'pixel-gallery' ), sub: __( 'From BdThemes', 'pixel-gallery' ) },
		! config.isPro && { to: '/get-pro', icon: RocketIcon, accent: 'green', label: __( 'Get Pro', 'pixel-gallery' ), sub: __( 'Unlock every widget', 'pixel-gallery' ) },
	]
		.filter( Boolean )
		.slice( 0, 6 );

	const mostUsed = [ ...used ].sort( ( a, b ) => b.usage_count - a.usage_count ).slice( 0, 5 );

	return (
		<div>
			<div className="flex items-end justify-between gap-4 flex-wrap mb-[22px]">
				<div>
					<h1 className="text-[31px] leading-[1.15] font-extrabold tracking-[-.025em] text-[#0f1115] dark:text-ink-text">
						{ welcome }
					</h1>
					<p className="text-[14.5px] text-[#6c6c75] dark:text-ink-muted mt-[6px]">
						{ __( 'Here’s what’s happening with your galleries.', 'pixel-gallery' ) }
					</p>
				</div>
				<div className="flex items-center gap-[10px]">
					<a
						href={ config.setupWizardUrl }
						className="inline-flex items-center gap-[7px] rounded-full py-[10px] px-[15px] text-[13px] font-bold bg-white dark:bg-ink-card border border-[#e2e2e6] dark:border-[#2a313c] text-[#3f3f46] dark:text-[#cdd0d6] transition-all hover:border-brand hover:text-brand"
					>
						<RocketIcon size={ 15 } />
						{ __( 'Setup wizard', 'pixel-gallery' ) }
					</a>
					<a
						href={ config.newPageUrl }
						className="inline-flex items-center gap-[7px] rounded-full py-[10px] px-[15px] text-[13px] font-extrabold bg-gradient-brand text-white shadow-glow transition-all hover:-translate-y-px hover:shadow-glow-lg"
					>
						<PlusIcon size={ 15 } />
						{ __( 'Create new page', 'pixel-gallery' ) }
					</a>
				</div>
			</div>

			<div className="grid grid-cols-4 max-[1024px]:grid-cols-2 max-[640px]:grid-cols-1 gap-4">
				<DashboardStatCard
					icon={ GridIcon }
					accent="rose"
					value={ widgets.length }
					label={ __( 'Total widgets', 'pixel-gallery' ) }
					pill={ tracking ? sprintf( /* translators: %d: used widget count */ __( '%d used', 'pixel-gallery' ), used.length ) : null }
					pillTone="green"
				/>
				<DashboardStatCard
					icon={ ZapIcon }
					accent="green"
					value={ enabled }
					label={ __( 'Widgets enabled', 'pixel-gallery' ) }
					pill={ __( 'live', 'pixel-gallery' ) }
					pillTone="blue"
				/>
				<DashboardStatCard
					icon={ PuzzleIcon }
					accent="woo"
					value={ extensionsOn }
					label={ __( 'Extensions active', 'pixel-gallery' ) }
					pill={ sprintf( /* translators: %d: total */ __( 'of %d', 'pixel-gallery' ), extensions.length ) }
				/>
				<DashboardStatCard
					icon={ SparklesIcon }
					accent="amber"
					value={ proWidgets.length }
					label={ __( 'Pro widgets', 'pixel-gallery' ) }
					pill={ config.isPro ? __( 'unlocked', 'pixel-gallery' ) : __( 'locked', 'pixel-gallery' ) }
					pillTone={ config.isPro ? 'green' : undefined }
				/>
			</div>

			<HealthStrip metrics={ metrics } />

			<div className="grid grid-cols-[1.6fr_1fr] max-[1024px]:grid-cols-1 gap-4 mt-4 items-start">
				<div className="flex flex-col gap-4">
					<QuickActions actions={ actions } />
					<MostUsedCard widgets={ mostUsed } />
					<ActivityCard />
				</div>
				<div className="flex flex-col gap-4">
					<SuggestionsCard />
					<PromoCard />
					<SystemStatusCard />
					<ResourcesCard />
				</div>
			</div>
		</div>
	);
}
