/* global woodmart_settings */
(function($) {
	woodmartThemeModule.$document.on('wdShopPageInit', function () {
		woodmartThemeModule.loginSidebar();
	});

	woodmartThemeModule.loginSidebar = function() {
		var body = woodmartThemeModule.$body;
		var loginFormSide = $('.login-form-side');
		var closeSide = $('.wd-close-side');

		woodmartThemeModule.$document.on('click', '.login-side-opener', function(e) {
				if (!loginFormSide.length) {
					return
				}

				e.preventDefault();

				if (isOpened()) {
					closeWidget();
				} else {
					setTimeout(function() {
						openWidget();
					}, 10);
				}
			});

		body.on('click touchstart', '.wd-close-side', function() {
			if (isOpened()) {
				closeWidget();
			}
		});

		body.on('click', '.close-side-widget', function(e) {
			e.preventDefault();
			if (isOpened()) {
				closeWidget();
			}
		});

		woodmartThemeModule.$document.on('keyup', function(e) {
			if (e.keyCode === 27 && isOpened()) {
				closeWidget();
			}
		});

		var closeWidget = function() {
			loginFormSide.trigger('wdCloseSide');
			loginFormSide.removeClass('wd-opened');
			closeSide.removeClass('wd-close-side-opened');
		};

		var openWidget = function() {
			loginFormSide.trigger('wdOpenSide');
			loginFormSide.find('form').removeClass('hidden-form');
			loginFormSide.addClass('wd-opened');
			closeSide.addClass('wd-close-side-opened');
		};

		if (loginFormSide.find('.woocommerce-notices-wrapper > ul').length > 0) {
			openWidget();
		}

		var isOpened = function() {
			return loginFormSide.hasClass('wd-opened');
		};
	};

	$(document).ready(function() {
		woodmartThemeModule.loginSidebar();
	});
})(jQuery);
