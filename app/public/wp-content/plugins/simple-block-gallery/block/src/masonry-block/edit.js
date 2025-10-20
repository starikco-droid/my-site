import { __ } from '@wordpress/i18n';
import { RangeControl, Button, PanelBody, ToggleControl } from '@wordpress/components';
import { InspectorControls, InnerBlocks, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { RawHTML } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	const onUpdateImage = ( image ) => {
		setAttributes( {
			image: image,
			images_ids: List_Ids( image ),
			list_images: List_Images( image )
		} );
	};

	function List_Ids( image ) {
		let j = [];
		for( let i in image ) {
			j.push( image[i].id );
		}
		return j;
	}

	function List_Images( image ) {
		const unique_id = Math.floor( Math.random() * 90000 ) + 10000;
		let j = '<!-- wp:paragraph -->';
		j += '<style type="text/css">';
		j += '.simple-block-gallery-masonry' + unique_id + ' { display: block; column-width: ' + attributes.width + 'px; column-gap: 0; padding: 0; }';
		j += 'div.masonry' + unique_id + ' { display: block; padding: ' + attributes.padding + 'px; }';
		j += 'div.masonry' + unique_id + ' img { display: block; width: 100%; border-radius: ' + attributes.r_images + 'px; }';
		if ( 120 <= attributes.width ) {
			j += 'div.masonry' + unique_id + ' figure { position: relative; }';
			j += 'div.masonry' + unique_id + ' figcaption { position: absolute; bottom: 0; left: 0; right: 0; width: 100%; background: rgba(0, 0, 0, 0.6); color: #fff; text-align: center; box-sizing: border-box; font-size: 0.7em; opacity: 0; transition: opacity 0.4s ease; border-radius: ' + attributes.r_images + 'px;  pointer-events: none; }';
			j += 'div.masonry' + unique_id + ' figure:hover figcaption,' + 'div.masonry' + unique_id + ' figure.show-caption figcaption { opacity: 1; pointer-events: auto; }';
			j += 'div.masonry' + unique_id + ' @media (max-width: 768px) { figure figcaption { opacity: 1; } }';
		}
		j += '.wp-block-image, figure figcaption { margin: 0 !important; padding: 0 !important; }';
		j += '</style>';
		j += '<div class="simple-block-gallery-masonry' + unique_id + '">';
		for( let i in image ) {
			j += '<div class="masonry' + unique_id + '">';
			j += '<!-- wp:image {"lightbox":{"enabled":' + attributes.link + '},"id":' + image[i].id + ',"sizeSlug":"large","linkDestination":"none"} -->';
			j += '<figure class="wp-block-image size-large">';
			j += '<img src="' + image[i].url + '" alt="' + image[i].alt + '">';
			if ( image[i].caption && 120 <= attributes.width ) {
				j += '<figcaption>';
				j += image[i].caption;
				j += '</figcaption>';
			}
			j += '</figure>';
			j += '<!-- /wp:image -->';
			j += '</div>';
		}
		j += '</div>';
		j += '<!-- /wp:paragraph -->';
		return j;
	}

	attributes.list_images = List_Images( attributes.image );

	const { preview } = attributes;
	if ( preview ) {
		return (
			<div className="simple-block-gallery-block-preview">
				<img src = { simple_block_gallery_preview_masonry.url } alt="Preview" />
			</div>
		);
	}

	const media_upload = [];
	media_upload.push(
		<div className="simple-block-gallery-block-placeholder">
			{ ! attributes.images_ids && (
				<>
					<div><strong>Simple Block Gallery</strong></div>
					<div>{ __( 'Masonry Block', 'simple-block-gallery' ) }</div>
					<div>{ __( 'Add your gallery here.', 'simple-block-gallery' ) }</div>
				</>
			) }
			<MediaUploadCheck>
				<MediaUpload
					title = { __( 'Masonry Block', 'simple-block-gallery' ) }
					onSelect = { onUpdateImage }
					allowedTypes = 'image'
					gallery = { true }
					multiple = { true }
					value = { attributes.images_ids }
					render = { ( { open } ) => (
						<Button
							variant = "secondary"
							onClick = { open }
						>
							{ ! attributes.images_ids ? __( 'Create Gallery', 'simple-block-gallery' ) : __( 'Update gallery', 'simple-block-gallery' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);

	return (
		<div { ...blockProps }>
			<RawHTML>{ attributes.list_images }</RawHTML>
			{ media_upload }
			<InspectorControls>
				<PanelBody title = { __( 'Settings', 'simple-block-gallery' ) } initialOpen = { true }>
					{ media_upload }
					<hr />
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label = { __( 'Width', 'simple-block-gallery' ) }
						help = { __( 'If there is a caption, it will be overlaid with a size of 120 or larger on mouse-over/tap.', 'simple-block-gallery' ) }
						max = { 1000 }
						min = { 10 }
						value = { attributes.width }
						onChange = { ( value ) => setAttributes( { width: value } ) }
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label = { __( 'Space', 'simple-block-gallery' ) }
						max = { 20 }
						min = { 0 }
						value = { attributes.padding }
						onChange = { ( value ) => setAttributes( { padding: value } ) }
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label = { __( 'Rounded Images', 'simple-block-gallery' ) }
						max = { 20 }
						min = { 0 }
						value = { attributes.r_images }
						onChange = { ( value ) => setAttributes( { r_images: value } ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label = { __( 'Expand on click', 'simple-block-gallery' ) }
						help = { __( 'Scales the image with a lightbox effect', 'simple-block-gallery' ) }
						checked = { attributes.link }
						onChange = { ( value ) => setAttributes( { link: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
