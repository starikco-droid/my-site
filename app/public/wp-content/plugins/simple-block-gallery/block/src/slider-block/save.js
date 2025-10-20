import { useBlockProps } from '@wordpress/block-editor';
import { Fragment, RawHTML } from '@wordpress/element';

export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();
	return (
		<Fragment { ...blockProps }>
			{
				attributes.list_images && (
					<RawHTML>{ attributes.list_images }</RawHTML>
				)
			}
		</Fragment>
	);
}
