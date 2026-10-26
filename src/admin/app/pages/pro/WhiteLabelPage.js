/**
 * White Label (Pixel Gallery Pro), laid out like Element Pack's White Label
 * page. `data` comes from Pro through the `pixel_gallery/admin/react_pages`
 * filter; without it the route shows the PHP panel instead.
 */
import { __ } from '@wordpress/i18n';
import PageHeader from '../../components/shell/PageHeader';
import WhiteLabelPanel from './white-label/WhiteLabelPanel';

export default function WhiteLabelPage( { data } ) {
	return (
		<div>
			<PageHeader
				title={ __( 'White Label', 'pixel-gallery' ) }
				subtitle={ __(
					'Rebrand Pixel Gallery across the admin for your clients.',
					'pixel-gallery'
				) }
			/>
			<WhiteLabelPanel data={ data || {} } />
		</div>
	);
}
