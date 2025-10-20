/* global woodmart_settings */
(function($) {
	woodmartThemeModule.$document.on('wdHiddenSidebarsInited', function() {
		woodmartThemeModule.lazyLoading();
	});

	$.each([
		'frontend/element_ready/wd_products.default',
		'frontend/element_ready/wd_products_tabs.default',
		'frontend/element_ready/wd_product_categories.default',
		'frontend/element_ready/wd_products_brands.default',
		'frontend/element_ready/wd_blog.default',
		'frontend/element_ready/wd_images_gallery.default',
		'frontend/element_ready/wd_product_categories.default',
		'frontend/element_ready/wd_slider.default',
		'frontend/element_ready/wd_banner_carousel.default',
		'frontend/element_ready/wd_banner.default',
		'frontend/element_ready/wd_infobox_carousel.default',
		'frontend/element_ready/wd_infobox.default',
		'frontend/element_ready/wd_instagram.default',
		'frontend/element_ready/wd_testimonials.default'
	], function(index, value) {
		woodmartThemeModule.wdElementorAddAction(value, function() {
			woodmartThemeModule.lazyLoading();
		});
	});

	woodmartThemeModule.lazyLoading = function() {
		if (!window.addEventListener || !window.requestAnimationFrame || !document.getElementsByClassName) {
			return;
		}

		var pItem = document.querySelectorAll('img[data-src], source[data-srcset]');
		var bgItem = document.querySelectorAll('.wd-lazy-bg');
		var videoItem = document.querySelectorAll('video[data-poster]');
		var pCount;
		var bgCount;
		var timer;

		woodmartThemeModule.$document.on('wood-images-loaded added_to_cart updated_cart_totals updated_checkout wc_fragments_refreshed', function() {
			pItem = document.querySelectorAll('img[data-src], source[data-srcset]');
			bgItem = document.querySelectorAll('.wd-lazy-bg');
			videoItem = document.querySelectorAll('video[data-poster]');

			inView();
		});

		// Fix for menu.
		woodmartThemeModule.$body.on('click', '.wd-header-mobile-nav > a, .wd-nav-opener, .wd-btn-show-cat', function() {
			woodmartThemeModule.$document.trigger('wood-images-loaded');
		});

		$('.wd-scroll-content').on('scroll', function() {
			woodmartThemeModule.$document.trigger('wood-images-loaded');
		});

		// WooCommerce tabs fix
		$('.wc-tabs > li').on('click', function() {
			woodmartThemeModule.$document.trigger('wood-images-loaded');
		});

		$('.wd-side-hidden').on('scroll', function () {
			woodmartThemeModule.$document.trigger('wood-images-loaded');
		});

		// scroll and resize events
		window.addEventListener('scroll', scroller, false);
		window.addEventListener('resize', scroller, false);

		// DOM mutation observer
		if (MutationObserver) {
			var observer = new MutationObserver(function() {
				if (pItem.length !== pCount) {
					inView();
				}
			});

			observer.observe(document.body, {
				subtree      : true,
				childList    : true,
				attributes   : true,
				characterData: true
			});
		}

		// initial check
		inView();

		// throttled scroll/resize
		function scroller() {
			timer = timer || setTimeout(function() {
				timer = null;
				inView();
			}, 100);
		}

		// image in view?
		function inView() {
			if (pItem.length || bgItem.length || videoItem.length) {
				requestAnimationFrame(function() {
					var offset = parseInt(woodmart_settings.lazy_loading_offset);
					var wT = window.pageYOffset, wB = wT + window.innerHeight + offset, cRect, pT, pB, p = 0, b = 0;

					if (pItem.length) {
						while (p < pItem.length) {
							cRect = pItem[p].getBoundingClientRect();
							pT = wT + cRect.top;
							pB = pT + cRect.height;

							if (wT < pB && wB > pT && !pItem[p].loaded) {
								loadFullImage(pItem[p], p);
							} else {
								p++;
							}
						}

						pCount = pItem.length;
					}

					if (bgItem.length) {
						while (b < bgItem.length) {
							cRect = bgItem[b].getBoundingClientRect();
							pT = wT + cRect.top;
							pB = pT + cRect.height;

							if (wT < pB && wB > pT && bgItem[b].classList.contains('wd-lazy-bg')) {
								bgItem[b].classList.remove('wd-lazy-bg');
							} else {
								b++;
							}
						}

						bgCount = bgItem.length;
					}

					if (videoItem.length) {
						var v = 0;

						while (v < videoItem.length) {
							cRect = videoItem[v].getBoundingClientRect();
							pT = wT + cRect.top;
							pB = pT + cRect.height;

							if (wT < pB && wB > pT && !videoItem[v].loaded) {
								videoItem[v].poster = videoItem[v].dataset.poster;

								videoItem[v].loaded = true;
							} else {
								v++;
							}
						}
					}
				});
			}
		}

		// replace with full image
		function loadFullImage(item) {
			item.onload = addedImg;

			if (item.querySelector('img') !== null) {
				item.querySelector('img').onload = addedImg;
				item.querySelector('img').src = item.dataset.src;
				item.querySelector('source').srcset = item.dataset.src;

				if (typeof (item.dataset.srcset) != 'undefined') {
					item.querySelector('img').srcset = item.dataset.srcset;
				}
			}

			if (typeof (item.dataset.src) != 'undefined') {
				item.src = item.dataset.src;
			}

			if (typeof (item.dataset.srcset) != 'undefined') {
				item.srcset = item.dataset.srcset;
			}

			item.loaded = true;

			// replace image
			function addedImg() {
				requestAnimationFrame(function() {
					if (item.classList.contains('wd-lazy-fade') || item.classList.contains('wd-lazy-blur')) {
						item.classList.add('wd-loaded');
					}

					var picture = item.closest('picture')

					if ( picture && (picture.classList.contains('wd-lazy-fade') || picture.classList.contains('wd-lazy-blur')) ) {
						picture.classList.add('wd-loaded')
					}

					var $masonry = jQuery(item).parents('.grid-masonry, .wd-masonry');
					if ($masonry.length > 0 && $masonry.data( 'isotope' )) {
						$masonry.isotope('layout');
					}
					var $categories = jQuery(item).parents('.wd-cats-element .wd-masonry');
					if ($categories.length > 0) {
						$categories.packery();
					}
				});
			}
		}
	};

	$(document).ready(function() {
		woodmartThemeModule.lazyLoading();
	});
})(jQuery);
