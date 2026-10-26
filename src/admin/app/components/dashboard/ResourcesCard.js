/**
 * Dashboard card: documentation, video tutorials, support, feature requests
 * and the setup wizard.
 */
import { __ } from '@wordpress/i18n';
import config from '../../utils/config';
import IconTile from '../ui/IconTile';
import { BookIcon, PlayIcon, HeadphonesIcon, SparklesIcon, RocketIcon, ChevronRightIcon, ExternalIcon } from '../ui/Icons';

export default function ResourcesCard() {
	if ( config.whiteLabel ) {
		return null;
	}

	const links = [
		{ title: __( 'Documentation', 'pixel-gallery' ), sub: __( 'Guides & tutorials', 'pixel-gallery' ), icon: BookIcon, accent: 'blue', url: config.docsUrl },
		{ title: __( 'Video tutorials', 'pixel-gallery' ), sub: __( 'Learn on YouTube', 'pixel-gallery' ), icon: PlayIcon, accent: 'rose', url: config.videosUrl },
		{ title: __( 'Support', 'pixel-gallery' ), sub: __( 'Get help from the team', 'pixel-gallery' ), icon: HeadphonesIcon, accent: 'violet', url: config.supportUrl },
		{ title: __( 'Request a feature', 'pixel-gallery' ), sub: __( 'Tell us what to build next', 'pixel-gallery' ), icon: SparklesIcon, accent: 'amber', url: config.featureUrl },
		{ title: __( 'Setup wizard', 'pixel-gallery' ), sub: __( 'Guided configuration', 'pixel-gallery' ), icon: RocketIcon, accent: 'green', url: config.setupWizardUrl, sameTab: true },
	].filter( ( link ) => !! link.url );

	return (
		<div className="bg-white dark:bg-ink-card border border-[#e9e9ed] dark:border-ink-border rounded-card p-5">
			<div className="text-[17px] font-extrabold text-[#0f1115] dark:text-ink-text mb-[14px]">
				{ __( 'Resources & help', 'pixel-gallery' ) }
			</div>
			<div className="flex flex-col gap-[9px]">
				{ links.map( ( link ) => (
					<a
						key={ link.title }
						href={ link.url }
						target={ link.sameTab ? undefined : '_blank' }
						rel={ link.sameTab ? undefined : 'noreferrer' }
						className="group flex items-center gap-3 p-[11px] border border-[#eef0f3] dark:border-ink-border rounded-[13px] cursor-pointer transition-all duration-150 hover:border-brand hover:bg-[#f3f9fd] dark:hover:bg-[#0c2433]"
					>
						<IconTile icon={ link.icon } accent={ link.accent } size="md" />
						<div className="flex-1 min-w-0">
							<div className="text-[13px] font-bold text-[#27272a] dark:text-ink-text transition-colors group-hover:text-brand">{ link.title }</div>
							<div className="text-[11.5px] text-[#6d6d74] dark:text-ink-muted mt-px">{ link.sub }</div>
						</div>
						{ link.sameTab ? (
							<ChevronRightIcon size={ 16 } className="text-[#c4c4cc] flex-none transition-all duration-150 group-hover:text-brand group-hover:translate-x-[3px]" />
						) : (
							<ExternalIcon size={ 15 } className="text-[#c4c4cc] flex-none transition-colors duration-150 group-hover:text-brand" />
						) }
					</a>
				) ) }
			</div>
		</div>
	);
}
