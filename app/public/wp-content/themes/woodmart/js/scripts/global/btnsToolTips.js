/* global woodmart_settings */
(function($) {
	woodmartThemeModule.$document.on('wdBackHistory wdProductsTabsLoaded wdSearchFullScreenContentLoaded wdActionAfterAddToCart wdShopPageInit wdArrowsLoadProducts wdLoadMoreLoadProducts wdUpdateWishlist wdQuickViewOpen wdQuickShopSuccess wdProductBaseHoverIconsResize wdRecentlyViewedProductLoaded updated_checkout updated_cart_totals', function () {
		woodmartThemeModule.btnsToolTips();
	});

	woodmartThemeModule.$document.on('wdUpdateTooltip', function (e, $this) {
		woodmartThemeModule.updateTooltip($this);
	});

	$.each([
		'frontend/element_ready/wd_products.default',
		'frontend/element_ready/wd_products_tabs.default',
		'frontend/element_ready/wd_single_product_nav.default',
		'frontend/element_ready/wd_single_product_size_guide_button.default',
		'frontend/element_ready/wd_single_product_compare_button.default',
		'frontend/element_ready/wd_single_product_wishlist_button.default'
	], function(index, value) {
		woodmartThemeModule.wdElementorAddAction(value, function() {
			woodmartThemeModule.btnsToolTips();
		});
	});

	woodmartThemeModule.btnsToolTips = function() {
		// Bootstrap tooltips
		$(woodmart_settings.tooltip_top_selector).on('mouseenter', function() {
			initTooltip($(this), 'top');
		});
		document.querySelectorAll(woodmart_settings.tooltip_top_selector).forEach(el => {
			el.addEventListener('touchstart', function(event) {
				initTooltip($(this), 'top');
			}, { passive: true });
		});

		$(woodmart_settings.tooltip_left_selector).on('mouseenter', function() {
			initTooltip($(this), woodmartThemeModule.$body.hasClass('rtl') ? 'right' : 'left');
		});
		document.querySelectorAll(woodmart_settings.tooltip_left_selector).forEach(el => {
			el.addEventListener('touchstart', function(event) {
				initTooltip($(this), woodmartThemeModule.$body.hasClass('rtl') ? 'right' : 'left');
			}, { passive: true });
		});

		function initTooltip( $this, placement ) {
			if ((! $this.hasClass('wd-hint') && ! $this.closest('.wd-review-likes').length && woodmartThemeModule.windowWidth <= 1024) || $this.hasClass('wd-tooltip-inited') || $this.hasClass('wd-with-html')) {
				return;
			}

			$this.tooltip({
				animation: false,
				container: 'body',
				trigger  : 'hover',
				boundary: 'window',
				placement: placement,
				title    : function () {
					var $this = $(this);

					if ($this.find('.added_to_cart').length > 0) {
						return $this.find('.add_to_cart_button').text();
					}

					if ($this.find('.add_to_cart_button').length > 0) {
						return $this.find('.add_to_cart_button').text();
					}

					if ($this.find('.wd-swatch-text').length > 0) {
						return $this.find('.wd-swatch-text').text();
					}

					if ($this.closest('.wd-review-likes').length) {
						return woodmart_settings.review_likes_tooltip;
					}

					return $this.text();
				}
			});

			$this.tooltip('show');

			$this.addClass('wd-tooltip-inited');
		}

		$('.wd-tooltip.wd-with-html').each(function() {
			var $this = $(this);
			var timeout;

			$this.on('mouseenter touchstart', { passive: true }, function() {
				if (!$(this).hasClass('wd-tooltip-inited')) {
					initHtmlTooltips($this);
				}

				$this.tooltip('show');

				$('#' + $this.attr('aria-describedby'))
					.on('mouseenter touchstart', { passive: true }, function() {
						clearTimeout(timeout);
					})
					.on('mouseleave touchend', { passive: true }, function() {
						clearTimeout(timeout);

						timeout = setTimeout(function() {
							$this.tooltip('hide');
						}, 100);
					});
			});

			$this.on('mouseleave touchend', { passive: true }, function() {
				clearTimeout(timeout);

				timeout = setTimeout(function() {
					$this.tooltip('hide');

					$('#' + $this.attr('aria-describedby')).off('mouseenter mouseleave touchstart touchend');
				}, 100);
			});
		});

		function initHtmlTooltips($el) {
			$el.tooltip({
				animation: false,
				container: 'body',
				trigger: 'manual',
				boundary: 'window',
				placement: 'top',
				sanitize: false,
				html: true,
				title: function() {
					return $(this).html();
				}
			});

			$el.addClass('wd-tooltip-inited');
		}
	};

	woodmartThemeModule.updateTooltip = function($this) {
		var $tooltip = $($this);

		if ( !$tooltip.hasClass('wd-tooltip-inited') ) {
			$tooltip = $tooltip.parent('.wd-tooltip-inited');
		}

		if (woodmartThemeModule.windowWidth <= 1024 || !$tooltip.hasClass('wd-tooltip-inited') || 'undefined' === typeof ($.fn.tooltip) || !$tooltip.is(':hover')) {
			return;
		}

		$tooltip.tooltip('show');
	};

	$(document).ready(function() {
		woodmartThemeModule.btnsToolTips();
	});
})(jQuery);

