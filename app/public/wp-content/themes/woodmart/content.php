<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 */
// woodmart_setup_loop();

$woodmart_loop         = woodmart_loop_prop( 'woodmart_loop' );
$is_large_image        = 'large_image' === woodmart_get_opt( 'single_post_design' );
$post_format           = get_post_format();
$thumb_classes         = '';
$gallery_slider        = apply_filters( 'woodmart_gallery_slider', true );
$gallery               = array();
$gallery_inner_classes = '';

$classes = array(
	'post-single-page',
);

if ( is_singular( 'post' ) && $is_large_image ) {
	$classes[] = 'post-single-large-image';
}

if ( ! get_the_title() ) {
	$classes[] = 'post-no-title';
}

if ( 'gallery' === $post_format && $gallery_slider ) {
	$gallery = get_post_gallery( false, false );

	if ( ! empty( $gallery['src'] ) ) {
		$thumb_classes         .= ' wd-carousel-container wd-post-gallery';
		$gallery_inner_classes .= ' color-scheme-light';
	}
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<header class="wd-single-post-header">
		<?php if ( ! $is_large_image ) : ?>
			<?php if ( woodmart_loop_prop( 'parts_meta' ) && get_the_category_list( ', ' ) ) : ?>
				<div class="wd-post-cat wd-style-with-bg">
					<?php echo wp_kses( get_the_category_list( ', ' ), true ); ?>
				</div>
			<?php endif ?>

			<?php if ( woodmart_loop_prop( 'parts_title' ) ) : ?>
				<h1 class="wd-entities-title wd-post-title title"><?php the_title(); ?></h1>
			<?php endif; ?>

			<?php if ( woodmart_loop_prop( 'parts_meta' ) ) : ?>
				<?php
				woodmart_enqueue_inline_style( 'blog-mod-author' );
				woodmart_enqueue_inline_style( 'blog-mod-comments-button' );
				?>
				<div class="wd-post-meta">
					<div class="wd-post-author wd-meta-author">
						<?php woodmart_post_meta_author( true, 'long', true, 22 ); ?>
					</div>

					<?php if ( woodmart_get_opt( 'blog_published_date', true ) ) : ?>
						<div class="wd-modified-date">
							<?php woodmart_post_modified_date(); ?>
						</div>

						<div class="wd-post-date wd-style-default">
							<time class="published" datetime="<?php echo get_the_date( 'c' ); // phpcs:ignore ?>">
								<?php echo esc_html( _x( 'On', 'meta-date', 'woodmart' ) ) . ' ' . get_the_date(); ?>
							</time>
						</div>
					<?php endif; ?>

					<?php if ( comments_open() || pings_open() ) : ?>
					<div class="wd-post-reply wd-style-1">
						<?php woodmart_post_meta_reply(); ?>
					</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( ( has_post_thumbnail() || ! empty( $gallery['src'] ) ) && ! post_password_required() && ! is_attachment() && woodmart_loop_prop( 'parts_media' ) ) : ?>
			<div class="wd-post-image<?php echo esc_attr( $thumb_classes ); ?>">
				<?php if ( 'gallery' === $post_format && $gallery_slider && ! empty( $gallery['src'] ) ) : ?>
					<?php
					woodmart_enqueue_js_library( 'swiper' );
					woodmart_enqueue_js_script( 'swiper-carousel' );
					woodmart_enqueue_inline_style( 'swiper' );
					woodmart_enqueue_inline_style( 'blog-mod-gallery' );
					?>
					<div class="wd-carousel-inner<?php echo esc_attr( $gallery_inner_classes ); ?>">
						<div class="wd-carousel wd-grid"<?php echo woodmart_get_carousel_attributes( array( 'autoheight' => 'yes' ) ); //phpcs:ignore ?>>
							<div class="wd-carousel-wrap">
								<?php
								foreach ( $gallery['src'] as $src ) {
									if ( preg_match( '/data:image/is', $src ) ) {
										continue;
									}
									?>
										<div class="wd-carousel-item">
										<?php echo apply_filters( 'woodmart_image', '<img src="' . esc_url( $src ) . '" />' ); ?>
										</div>
										<?php
								}
								?>
							</div>
						</div>
						<?php woodmart_get_carousel_nav_template( ' wd-post-arrows wd-pos-sep wd-custom-style' ); ?>
					</div>
				<?php elseif ( ! $is_large_image ) : ?>
					<?php the_post_thumbnail(); ?>
				<?php endif ?>
				</div>
		<?php endif; ?>
	</header>

	<?php if ( woodmart_loop_prop( 'parts_text' ) ) : ?>
		<div class="wd-entry-content<?php echo woodmart_get_old_classes( ' woodmart-entry-content' ); //phpcs:ignore. ?>">
			<?php woodmart_get_content( woodmart_loop_prop( 'parts_btn' ), true ); ?>

			<?php
				wp_link_pages(
					array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'woodmart' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					)
				);
			?>
		</div>
	<?php endif; ?>

</article>

<?php if ( get_the_author_meta( 'description' ) ) : ?>
	<?php get_template_part( 'author-bio' ); ?>
<?php endif; ?>


<?php
// Increase loop count
woodmart_set_loop_prop( 'woodmart_loop', $woodmart_loop + 1 );
