<?php
/**
 * Plugin Name: Simple Block Gallery
 * Plugin URI:  https://wordpress.org/plugins/simple-block-gallery/
 * Description: Add the effect of Masonry and Slider to images.
 * Version:     1.29
 * Author:      Katsushi Kawamori
 * Author URI:  https://riverforest-wp.info/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-block-gallery
 *
 * @package Simple Block Gallery
 */

/*
	Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$simpleblockgallery = new SimpleBlockGallery();

/** ==================================================
 * Main
 */
class SimpleBlockGallery {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'block_init' ) );

		/* Add lazy load */
		add_filter(
			'wp_img_tag_add_loading_attr',
			function ( $value, $image, $context ) {
				static $image_count = 0;
				if ( 'the_content' === $context ) {
					$image_count++;
					if ( 1 === $image_count ) {
						return 'eager';
					} else {
						return 'lazy';
					}
				}
			},
			10,
			3
		);

		/* Add img size for lazy load */
		add_filter( 'render_block', array( $this, 'add_img_size_attributes' ), 10, 2 );
	}

	/** ==================================================
	 * Block init
	 *
	 * @since 1.00
	 */
	public function block_init() {

		register_block_type(
			__DIR__ . '/block/build/sbg-parent-block',
			array(
				'title' => _x( 'Simple Block Gallery', 'block title', 'simple-block-gallery' ),
				'description' => _x( 'Generate a Masonry or Slider gallery.', 'block description', 'simple-block-gallery' ),
				'keywords' => array(
					_x( 'gallery', 'block keyword', 'simple-block-gallery' ),
					_x( 'masonry', 'block keyword', 'simple-block-gallery' ),
					_x( 'slider', 'block keyword', 'simple-block-gallery' ),
				),
			)
		);
		$script_parent_handle = generate_block_asset_handle( 'simple-block-gallery/sbg-parent-block', 'editorScript' );
		wp_set_script_translations( $script_parent_handle, 'simple-block-gallery' );
		wp_localize_script(
			$script_parent_handle,
			'simple_block_gallery_preview_parent',
			array(
				'url' => esc_url( 'https://ps.w.org/simple-block-gallery/assets/screenshot-1.png' ),
			)
		);

		register_block_type(
			__DIR__ . '/block/build/masonry-block',
			array(
				'title' => _x( 'Masonry Block', 'block title', 'simple-block-gallery' ),
				'description' => _x( 'Generate the masonry gallery.', 'block description', 'simple-block-gallery' ),
			)
		);
		$script_masonry_handle = generate_block_asset_handle( 'simple-block-gallery/masonry-block', 'editorScript' );
		wp_set_script_translations( $script_masonry_handle, 'simple-block-gallery' );
		wp_localize_script(
			$script_masonry_handle,
			'simple_block_gallery_preview_masonry',
			array(
				'url' => esc_url( 'https://ps.w.org/simple-block-gallery/assets/screenshot-2.png' ),
			)
		);

		register_block_type(
			__DIR__ . '/block/build/slider-block',
			array(
				'title' => _x( 'Slider Block', 'block title', 'simple-block-gallery' ),
				'description' => _x( 'Generate the slider gallery.', 'block description', 'simple-block-gallery' ),
			)
		);
		$script_slider_handle = generate_block_asset_handle( 'simple-block-gallery/slider-block', 'editorScript' );
		wp_set_script_translations( $script_slider_handle, 'simple-block-gallery' );
		wp_localize_script(
			$script_slider_handle,
			'simple_block_gallery_preview_slider',
			array(
				'url' => esc_url( 'https://ps.w.org/simple-block-gallery/assets/screenshot-3.png' ),
			)
		);
	}

	/** ==================================================
	 * Add img size for lazy load
	 *
	 * @param string $block_content  The block content.
	 * @param array  $block  The full block, including name and attributes.
	 * @return string $block_content  The block content.
	 * @since 1.21
	 */
	public function add_img_size_attributes( $block_content, $block ) {
		if ( 'core/image' === $block['blockName'] ) {
			$block_content = preg_replace_callback(
				'/<img([^>]+)>/',
				function ( $matches ) {
					$img_tag = $matches[0];
					if ( strpos( $img_tag, 'width=' ) !== false || strpos( $img_tag, 'height=' ) !== false ) {
						return $img_tag;
					}
					if ( preg_match( '/src="([^"]+)"/', $img_tag, $src_match ) ) {
						$src = $src_match[1];
						$attachment_id = attachment_url_to_postid( $src );
						if ( $attachment_id ) {
							$meta = wp_get_attachment_metadata( $attachment_id );
							if ( $meta && isset( $meta['width'], $meta['height'] ) ) {
								$img_tag = str_replace(
									'<img',
									'<img width="' . $meta['width'] . '" height="' . $meta['height'] . '"',
									$img_tag
								);
							}
						}
					}
					return $img_tag;
				},
				$block_content
			);
		}
		return $block_content;
	}
}
