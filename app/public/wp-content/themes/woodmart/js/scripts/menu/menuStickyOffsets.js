/* global woodmart_settings */
(function($) {
	woodmartThemeModule.menuStickyOffsets = function() {
		var $stickyNav = $('.wd-sticky-nav');
		var $side = $('.wd-close-side');

		$('.wd-sticky-nav .wd-nav-sticky.wd-nav-vertical').each(function() {
			var $menu = $(this);

			$menu.on('mouseenter mousemove', function() {
				if ($menu.hasClass('wd-offsets-calculated')) {
					return;
				}

				$menu.find('> .menu-item-has-children').each(function() {
					var $menuItem = $(this);

					if ($menuItem.find('> .wd-dropdown.wd-design-full-height').length) {
						return;
					}

					setOffset($menuItem);

					if ($menuItem.find('> .wd-dropdown').length) {
						$menuItem.find('.menu-item.menu-item-has-children').each(function() {
							setOffset($(this));
						});
					}
				});

				$menu.addClass('wd-offsets-calculated');
			});

			if ( 'undefined' === typeof woodmart_settings.clear_menu_offsets_on_resize || 'yes' === woodmart_settings.clear_menu_offsets_on_resize) {
				setTimeout(function () {
					woodmartThemeModule.$window.on('resize', woodmartThemeModule.debounce(function () {
						$menu.removeClass('wd-offsets-calculated');
						$menu.find(' > .menu-item-has-children > .wd-dropdown-menu').attr('style', '');
					}, 300));
				}, 2000);
			}

			var setOffset = function(li) {
				var $dropdown = li.find(' > .wd-dropdown');
				var dropdownHeight = $dropdown.innerHeight();
				var dropdownOffset = $dropdown.offset().top - woodmartThemeModule.$window.scrollTop();
				var viewportHeight = woodmartThemeModule.$window.height();
				var toTop = 0;

				$dropdown.attr('style', '');

				if (!dropdownHeight || !dropdownOffset) {
					return;
				}

				if ( dropdownOffset + dropdownHeight >= viewportHeight ) {
					toTop = dropdownOffset + dropdownHeight - viewportHeight;

					$dropdown.css({
						top: -toTop,
					});
				}
			}
		});

		woodmartThemeModule.$document.on('click', '.wd-header-sticky-nav', function(e) {
			e.preventDefault();

			var $stickyNavBtn = $(this);

			sideOpened( $stickyNavBtn, $stickyNavBtn.hasClass('wd-close-menu-mouseout') );
		});

		woodmartThemeModule.$document.on('mouseenter mousemove', '.wd-header-sticky-nav.wd-event-hover', function() {
			sideOpened( $(this) );
		});

		woodmartThemeModule.$document.on('click touchstart', '.wd-close-side.wd-location-sticky-nav', function() {
			closeSide();
		});

		function sideOpened( $stickyNavBtn, addMouseoutEvent = true ) {
			$stickyNavBtn.addClass('wd-opened');
			$stickyNav.addClass('wd-opened');

			$side.trigger('wdCloseSideAction', ['show', 'click', 'wd-location-sticky-nav']);

			if ( ! addMouseoutEvent ) {
				return;
			}

			$stickyNav.on('mouseout', function () {
				closeSide();

				$stickyNav.off('mouseout');
			});
		}

		function closeSide() {
			$('.wd-header-sticky-nav').removeClass('wd-opened');
			$stickyNav.removeClass('wd-opened');

			$side.trigger('wdCloseSideAction', ['hide', 'click', 'wd-location-sticky-nav']);
		}
	};

	window.addEventListener('wdEventStarted', function () {
		setTimeout(function () {
			woodmartThemeModule.menuStickyOffsets();
		}, 100);
	});
})(jQuery);
