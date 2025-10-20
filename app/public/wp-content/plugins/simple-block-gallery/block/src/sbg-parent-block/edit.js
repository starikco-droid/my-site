import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

const ALLOWED_BLOCKS = [ 'simple-block-gallery/masonry-block', 'simple-block-gallery/slider-block' ];

export default function Edit( { clientId } ) {
	const blockProps = useBlockProps();

	const hasChildBlocks = useSelect(
		(select) => {
			const block = select( 'core/block-editor' ).getBlock( clientId );
			return block?.innerBlocks?.length > 0;
		},
		[clientId]
	);

	return (
		<div { ...blockProps }
			style={{
				minHeight: '100px',
				border: hasChildBlocks ? 'none' : '2px dashed #ccc',
				padding: '1em',
				position: 'relative',
			}}
		>
			<InnerBlocks
				allowedBlocks = { ALLOWED_BLOCKS }
				templateLock = { false }
			/>
			{!hasChildBlocks && (
				<>
					<div className="simple-block-gallery-block-parent-description">
						<div className="simple-block-gallery-block-parent-title">Simple Block Gallery</div>
						{ __( 'Add your gallery here.', 'simple-block-gallery' ) }
					</div>
				</>
			)}
		</div>
	);
};
