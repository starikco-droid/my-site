<?php
$woodmart_loop  = woodmart_loop_prop( 'woodmart_loop' );
$blog_style     = woodmart_get_opt( 'blog_style', 'shadow' );
$post_format    = get_post_format();
$thumb_classes  = '';
$gallery_slider = apply_filters( 'woodmart_gallery_slider', true );
$gallery        = array();
$blog_excerpt   = woodmart_get_content( false, false, true );
$has_excerpt    = woodmart_loop_prop( 'parts_text' ) && ( get_the_excerpt() || $blog_excerpt );

$classes = array(
	'wd-post',
	'blog-design-' . woodmart_loop_prop( 'blog_design' ),
	'blog-post-loop',
);

if ( 'shadow' === $blog_style ) {
	$classes[] = 'blog-style-bg';

	if ( woodmart_get_opt( 'blog_with_shadow', true ) ) {
		$classes[] = 'wd-add-shadow';
	}
} else {
	$classes[] = 'blog-style-' . $blog_style;
}

if ( 'grid' === woodmart_loop_prop( 'blog_layout' ) ) {
	$classes[] = 'wd-col';
}

if ( ! get_the_title() ) {
	$classes[] = 'post-no-title';
}

if ( 'quote' === $post_format ) {
	woodmart_enqueue_inline_style( 'blog-loop-format-quote' );
} elseif ( 'gallery' === $post_format && $gallery_slider ) {
	$gallery = get_post_gallery( false, false );
	woodmart_enqueue_inline_style( 'blog-mod-gallery' );

	if ( ! empty( $gallery['ids'] ) ) {
		$thumb_classes = ' wd-carousel-container wd-post-gallery';

		$gallery['images_id'] = explode( ',', $gallery['ids'] );

		if ( ! has_post_thumbnail() ) {
			$classes[] = 'has-post-thumbnail';
		}
	}
}

if ( has_post_thumbnail() || ! empty( $gallery['ids'] ) ) {
	$thumb_classes .= ' color-scheme-light';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<?php if ( 'shadow' === $blog_style ) : ?>
		<div class="wd-post-inner">
	<?php endif; ?>

	<div class="wd-post-thumb<?php echo esc_html( $thumb_classes ); ?>">
		<?php if ( 'gallery' === $post_format && $gallery_slider && ! empty( $gallery['images_id'] ) ) : ?>
			<?php
			woodmart_enqueue_js_library( 'swiper' );
			woodmart_enqueue_js_script( 'swiper-carousel' );
			woodmart_enqueue_inline_style( 'swiper' );
			?>
			<div class="wd-carousel-inner">
				<div class="wd-carousel wd-grid"<?php echo woodmart_get_carousel_attributes( array( 'slides_per_view' => 1, 'autoheight' => 'yes' ) ); //phpcs:ignore ?>>
					<div class="wd-carousel-wrap">
						<?php
						foreach ( $gallery['images_id'] as $image_id ) {
							?>
							<div class="wd-carousel-item">
								<?php echo woodmart_otf_get_image_html( $image_id, apply_filters( 'woodmart_gallery_post_format_size', woodmart_get_opt('blog_image_size', 'large' ) ) ); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php woodmart_get_carousel_nav_template( ' wd-post-arrows wd-pos-sep wd-custom-style' ); ?>
			</div>
		<?php else : ?>
			<div class="wd-post-img">
				<?php echo woodmart_get_post_thumbnail(  woodmart_get_opt('blog_image_size', 'large' ) ); // phpcs:ignore ?>
			</div>
		<?php endif; ?>

		<?php /* translators: %s: Post title */ ?>
		<a class="wd-post-link wd-fill" tabindex="-1" href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark" aria-label="<?php echo esc_attr( sprintf( __( 'Link on post %s', 'woodmart' ), esc_attr( get_the_title() ) ) ); ?>"></a>

		<?php if ( woodmart_loop_prop( 'parts_meta' ) ) : ?>
			<div class="wd-post-header">
				<?php woodmart_enqueue_inline_style( 'blog-mod-author' ); ?>
				<div class="wd-post-author wd-meta-author">
					<?php woodmart_post_meta_author( true, false, true, 22 ); ?>
				</div>

				<div class="wd-post-actions">
					<?php if ( woodmart_is_social_link_enable( 'share' ) && function_exists( 'woodmart_shortcode_social' ) ) : ?>
						<div tabindex="0" class="wd-post-share wd-tltp">
							<div class="tooltip top">
								<div class="tooltip-inner">
									<?php
										echo woodmart_shortcode_social( // phpcs:ignore
											array(
												'size'  => 'small',
												'color' => 'light',
											)
										);
									?>
								</div>
								<div class="tooltip-arrow"></div>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( comments_open() ) : ?>
						<?php woodmart_enqueue_inline_style( 'blog-mod-comments-button' ); ?>
						<div class="wd-post-reply wd-meta-reply wd-style-1">
							<?php woodmart_post_meta_reply(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="wd-post-content">
		<div class="wd-post-meta wd-post-entry-meta">
			<?php if ( is_sticky() ) : ?>
				<div class="wd-featured-post"></div>
			<?php endif; ?>

			<?php if ( woodmart_loop_prop( 'parts_meta' ) && get_the_category_list( ', ' ) ) : ?>
				<div class="wd-post-cat wd-style-default">
					<?php echo get_the_category_list( ', ' ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>

			<?php if ( woodmart_loop_prop( 'parts_published_date', true ) ) : ?>
				<div class="wd-post-date wd-style-default">
					<time class="published" datetime="<?php echo get_the_date( 'c' ); // phpcs:ignore ?>">
						<?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
					</time>
				</div>

				<div class="wd-modified-date">
					<?php woodmart_post_modified_date(); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( woodmart_loop_prop( 'parts_title' ) ) : ?>
			<h3 class="wd-post-title wd-entities-title title post-title">
				<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
					<?php the_title(); ?>
				</a>
			</h3>
		<?php endif; ?>

		<?php if ( $has_excerpt ) : ?>
			<div class="wd-post-excerpt">
				<?php if ( is_search() ) : ?>
					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div>
				<?php else : ?>
					<?php
					echo $blog_excerpt; //phpcs:ignore

					wp_link_pages(
						array(
							'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'woodmart' ) . '</span>',
							'after'       => '</div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						)
					);
					?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( woodmart_loop_prop( 'parts_btn' ) ) : ?>
			<?php woodmart_render_read_more_btn( 'link' ); ?>
		<?php endif; ?>
	</div>

	<?php if ( 'shadow' === $blog_style ) : ?>
		</div>
	<?php endif; ?>
</article>
