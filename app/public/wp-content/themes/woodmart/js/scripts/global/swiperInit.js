woodmartThemeModule.$document.on('wdInstagramAjaxSuccess wdLoadDropdownsSuccess wdProductsTabsLoaded wdSearchFullScreenContentLoaded wdShopPageInit wdRecentlyViewedProductLoaded wdQuickViewOpen300', function() {
	woodmartThemeModule.carouselsInit();
});

[
	'frontend/element_ready/wd_products.default',
	'frontend/element_ready/wd_products_tabs.default',
	'frontend/element_ready/wd_product_categories.default',
	'frontend/element_ready/wd_products_brands.default',
	'frontend/element_ready/wd_blog.default',
	'frontend/element_ready/wd_portfolio.default',
	'frontend/element_ready/wd_images_gallery.default',
	'frontend/element_ready/wd_product_categories.default',
	'frontend/element_ready/wd_banner_carousel.default',
	'frontend/element_ready/wd_infobox_carousel.default',
	'frontend/element_ready/wd_instagram.default',
	'frontend/element_ready/wd_testimonials.default',
	'frontend/element_ready/wd_nested_carousel.default',
	'frontend/element_ready/wd_single_product_fbt_products.default',
	'frontend/element_ready/wd_slider.default',
].forEach( function (value) {
	woodmartThemeModule.wdElementorAddAction(value, function() {
		woodmartThemeModule.carouselsInit();
	});
});

woodmartThemeModule.carouselsInit = function() {
	if ('undefined' === typeof wdSwiper) {
		console.error('Swiper is not defined');

		return;
	}

	document.querySelectorAll('.wd-carousel-container > .wd-carousel-inner > .wd-carousel:not(.scroll-init)').forEach( function (carousel) {
		woodmartThemeModule.swiperInit(carousel);
	});

	const carouselOnScrollObserver = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				let carousel = entry.target;

				if (carousel && ! carousel.classList.contains('wd-initialized')) {
					woodmartThemeModule.swiperInit(carousel);
				}

				carouselOnScrollObserver.unobserve(carousel);
			}
		});
	}, { rootMargin: '200px 0px 200px 0px' });

	document.querySelectorAll('.wd-carousel-container > .wd-carousel-inner > .wd-carousel.scroll-init:not(.wd-initialized)').forEach((carousel) => {
		carouselOnScrollObserver.observe(carousel);
	});

	window.addEventListener('popstate', function() {
		document.querySelectorAll('.wd-carousel.wd-initialized').forEach( function (carousel) {
			if ('undefined' === typeof carousel.swiper) {
				carousel.classList.remove('wd-initialized');

				woodmartThemeModule.swiperInit(carousel);
			}
		});
	});
};

woodmartThemeModule.swiperInit = function(carousel, thumbs = false) {
	if ('undefined' === typeof wdSwiper) {
		console.error('Swiper is not defined');

		return;
	}

	if (carousel.closest('.woocommerce-product-gallery') && ! carousel.classList.contains('quick-view-gallery') || ( ! thumbs && 'undefined' !== typeof carousel.dataset.sync_child_id && document.querySelector('.wd-carousel[data-sync_parent_id=' + carousel.dataset.sync_child_id + ']') ) ) {
		return;
	}

	var carouselWrapper = carousel.closest('.wd-carousel-container');
	var carouselStyle = window.getComputedStyle(carousel);

	if (woodmartThemeModule.windowWidth <= 1024 && carouselWrapper.classList.contains('wd-carousel-dis-mb') || carousel.classList.contains('wd-initialized') ) {
		return;
	}

	var mainSlidesPerView = carouselStyle.getPropertyValue('--wd-col');
	var breakpointsSettings = woodmart_settings.carousel_breakpoints;
	var breakpoints = {};
	var carouselItemsLength = carousel.querySelectorAll('.wd-carousel-item').length;

	Object.entries(breakpointsSettings).forEach(( [size, key] ) => {
		var slidesPerView = carouselStyle.getPropertyValue('--wd-col-' + key );
		var enableScrollPerGroup = 'undefined' !== typeof carousel.dataset.scroll_per_page && 'yes' === carousel.dataset.scroll_per_page;

		if ( ! slidesPerView ) {
			slidesPerView = mainSlidesPerView;
		}

		if ( slidesPerView ) {
			breakpoints[ size ] = {
				slidesPerView : slidesPerView ? slidesPerView : 1
			};

			if ( 'yes' === carousel.dataset.wrap && parseInt(slidesPerView, 10 ) * 2 > carouselItemsLength || 'yes' === carousel.dataset.center_mode) {
				enableScrollPerGroup = false;
			}

			if ( enableScrollPerGroup && slidesPerView ) {
				breakpoints[ size ]['slidesPerGroup'] = parseInt(slidesPerView);
			}
		}
	});

	var config = {
		slidesPerView         : mainSlidesPerView,
		loop                  : 'yes' === carousel.dataset.wrap && (1 === parseInt(mainSlidesPerView, 10) || parseInt(mainSlidesPerView, 10) + 1 < carouselItemsLength),
		loopAddBlankSlides    : false,
		centeredSlides        : 'yes' === carousel.dataset.center_mode,
		autoHeight            : 'yes' === carousel.dataset.autoheight,
		grabCursor            : true,
		a11y                  : {
			enabled: true,
			prevSlideMessage: woodmart_settings.swiper_prev_slide_msg,
			nextSlideMessage: woodmart_settings.swiper_next_slide_msg,
			firstSlideMessage: woodmart_settings.swiper_first_slide_msg,
			lastSlideMessage: woodmart_settings.swiper_last_slide_msg,
			paginationBulletMessage: woodmart_settings.swiper_pagination_bullet_msg,
			slideLabelMessage: woodmart_settings.swiper_slide_label_msg,
		},
		breakpoints           : breakpoints,
		watchSlidesProgress   : true,
		slideClass            : 'wd-carousel-item',
		slideActiveClass      : 'wd-active',
		slideVisibleClass     : 'wd-slide-visible',
		slideNextClass        : 'wd-slide-next',
		slidePrevClass        : 'wd-slide-prev',
		slideFullyVisibleClass: 'wd-full-visible',
		slideBlankClass       : 'wd-slide-blank',
		lazyPreloaderClass    : 'wd-lazy-preloader',
		containerModifierClass: 'wd-',
		wrapperClass          : 'wd-carousel-wrap',
		simulateTouch         : ! carousel.closest('.block-editor-block-list__layout'),
		on                    : {
			init: function() {
				setTimeout(function() {
					woodmartThemeModule.$document.trigger('wdSwiperCarouselInited');
				}, 100);
			},
			slideChange: function() {
				woodmartThemeModule.$document.trigger('wood-images-loaded');
			}
		}
	};

	if ('undefined' !== typeof carousel.dataset.effect) {
		var effect = carousel.dataset.effect;

		if ('distortion' === effect) {
			effect = 'fade';
		}

		config.effect = effect;

		if ('parallax' === effect) {
			config.parallax = {
				enabled: true
			};

			carousel.querySelectorAll('.wd-slide-bg').forEach( function (slideBg) {
				slideBg.setAttribute('data-swiper-parallax', '50%');
			});
		}
	}

	if ('undefined' !== typeof carousel.dataset.sliding_speed && carousel.dataset.sliding_speed) {
		config.speed = carousel.dataset.sliding_speed;
	}

	var pagination = Array.prototype.filter.call(
		carouselWrapper.children,
		(element) => element.classList.contains('wd-nav-pagin-wrap'),
	).shift();

	if (pagination) {
		config.pagination = {
			el                     : pagination.querySelector('.wd-nav-pagin'),
			dynamicBullets         : pagination.classList.contains('wd-dynamic'),
			type                   : 'bullets',
			clickable              : true,
			bulletClass            : 'wd-nav-pagin-item',
			bulletActiveClass      : 'wd-active',
			modifierClass          : 'wd-type-',
			lockClass              : 'wd-lock',
			currentClass           : 'wd-current',
			totalClass             : 'wd-total',
			hiddenClass            : 'wd-hidden',
			clickableClass         : 'wd-clickable',
			horizontalClass        : 'wd-horizontal',
			verticalClass          : 'wd-vertical',
			paginationDisabledClass: 'wd-disabled',
			renderBullet           : function(index, className) {
				var innerContent = '';
				var label = woodmart_settings.swiper_pagination_bullet_msg.replace('{{index}}', index + 1);

				if (pagination.classList.contains('wd-style-number-2')) {
					innerContent = index + 1;

					if ( 9 >= innerContent ) {
						innerContent = '0' + innerContent;
					}
				}

				return '<li class="' + className + '" tabindex="0" aria-label="' + label + '"><span>' + innerContent + '</span></li>';
			}
		};
	}

	var navigationWrapper = Array.prototype.filter.call(
		carouselWrapper.querySelector('.wd-carousel-inner').children,
		(element) => element.classList.contains('wd-nav-arrows'),
	).shift();

	if (navigationWrapper) {
		config.navigation = {
			nextEl       : navigationWrapper.querySelector('.wd-btn-arrow.wd-next'),
			prevEl       : navigationWrapper.querySelector('.wd-btn-arrow.wd-prev'),
			disabledClass: 'wd-disabled',
			lockClass    : 'wd-lock',
			hiddenClass  : 'wd-hide'
		};
	}

	var scrollbar = Array.prototype.filter.call(
		carouselWrapper.children,
		(element) => element.classList.contains('wd-nav-scroll'),
	).shift();

	if (scrollbar) {
		config.scrollbar = {
			el                    : scrollbar,
			lockClass             : 'wd-lock',
			dragClass             : 'wd-nav-scroll-drag',
			scrollbarDisabledClass: 'wd-disabled',
			horizontalClass       : 'wd-horizontal',
			verticalClass         : 'wd-vertical',
			draggable             : true
		};

		config.on.scrollbarDragStart = function () {
			scrollbar.classList.add('wd-grabbing');
		};
		config.on.scrollbarDragEnd = function () {
			scrollbar.classList.remove('wd-grabbing');
		};
	}

	if ('undefined' !== typeof carousel.dataset.autoplay && 'yes' === carousel.dataset.autoplay) {
		config.autoplay = {
			delay: carousel.dataset.speed ? carousel.dataset.speed : 5000,
			pauseOnMouseEnter: true
		};
	}

	if ('undefined' !== typeof carousel.dataset.sync_parent_id) {
		var childCarousel = document.querySelector('.wd-carousel[data-sync_child_id=' + carousel.dataset.sync_parent_id + ']');

		if ( childCarousel ) {
			var childCarouselStyle = window.getComputedStyle(childCarousel);
			var mainSlidesPerViewChild = childCarouselStyle.getPropertyValue('--wd-col');

			if ( mainSlidesPerViewChild === mainSlidesPerView ) {
				config.controller = {
					control: woodmartThemeModule.swiperInit(childCarousel, true),
				}
			} else {
				config.thumbs = {
					swiper               : woodmartThemeModule.swiperInit(childCarousel, true),
					slideThumbActiveClass: 'wd-thumb-active',
					thumbsContainerClass : 'wd-thumbs'
				};
			}
		}
	}

	carousel.querySelectorAll('link').forEach(function (link) {
		var linkClone = link.cloneNode(false);

		carouselWrapper.append(linkClone);

		linkClone.addEventListener('load', function() {
			setTimeout(function () {
				link.remove();
			}, 500);
		}, false);
	});

	const swiper = new wdSwiper(carousel, config);

	if (config.controller) {
		swiper.controller.control.controller.control = swiper;
	}

	if (carouselWrapper && carouselWrapper.classList.contains('wd-slider')) {
		swiper.on('realIndexChange', function (swiper) {
			setTimeout(function () {
				carousel.dispatchEvent(new CustomEvent('wdSlideChange', {
					detail: {activeIndex: swiper.realIndex}
				}));
			},100);
		});
	}

	return swiper;
}

window.addEventListener('load',function() {
	woodmartThemeModule.carouselsInit();
});