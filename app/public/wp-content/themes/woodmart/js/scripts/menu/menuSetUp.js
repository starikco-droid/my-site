/* global woodmart_settings */
(function($) {
	woodmartThemeModule.menuSetUp = function() {
		var hasChildClass = 'menu-item-has-children',
		    mainMenu      = $('.wd-nav, .wd-header-cats, .wd-search-cat'),
		    openedClass   = 'wd-opened';

		$('.mobile-nav').find('ul.wd-nav-mobile').find(' > li').has('.wd-dropdown-menu').addClass(hasChildClass);

		woodmartThemeModule.$document.on('click', '.wd-nav .wd-event-click > a, .wd-header-cats.wd-event-click > span, .wd-search-cat-btn', function(e) {
			e.preventDefault();
			var $this = $(this);

			if ($this.parent().siblings().hasClass(openedClass)) {
				$this.parent().siblings().removeClass(openedClass);
			}

			$this.parent().toggleClass(openedClass);
		});

		woodmartThemeModule.$document.on('click', function(e) {
			var target = e.target;

			if (
				$('.' + openedClass).length > 0 &&
				!$(target).is('.wd-event-hover') &&
				!$(target).parents().is('.wd-event-hover') &&
				!$(target).parents().is('.' + openedClass + '') &&
				!$(target).is('.' + openedClass + '') &&
				!$(target).is('.wd-sticky-nav') &&
				! target.closest('.wd-cookies-popup') &&
				! target.closest('.wd-fb-holder') &&
				0 === $('.mfp-ready').length &&
				0 === $('.pswp--open').length
			) {
				mainMenu.find('.wd-event-click.' + openedClass + '').removeClass(openedClass);

				if (mainMenu.hasClass('wd-event-click')) {
					mainMenu.removeClass(openedClass);
				}

				if ($(target).closest('.wd-with-overlay').length) {
					return;
				}

				$('.wd-close-side').trigger('wdCloseSideAction', ['hide', 'click']);
			}
		});

		if ('yes' === woodmart_settings.menu_item_hover_to_click_on_responsive) {
			function menuIpadClick() {
				if (woodmartThemeModule.$window.width() <= 1024) {
					mainMenu.find(' > .menu-item-has-children.wd-event-hover').each(function() {
						$(this).data('original-event', 'hover').removeClass('wd-event-hover').addClass('wd-event-click');
					});
				} else {
					mainMenu.find(' > .wd-event-click').each(function() {
						var $this = $(this);

						if ($this.data('original-event') === 'hover') {
							$this.removeClass('wd-event-click').addClass('wd-event-hover');
						}
					});
				}
			}

			menuIpadClick();

			woodmartThemeModule.$window.on('resize', woodmartThemeModule.debounce(function() {
				menuIpadClick();
			}, 300));
		}
	};

	['wdEventStarted', 'wdUpdatedHeader'].forEach((eventName) => {
		window.addEventListener(eventName, function () {
			woodmartThemeModule.menuSetUp();
		});
	});
})(jQuery);
