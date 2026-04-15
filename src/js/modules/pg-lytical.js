(function ($, elementor) {

	'use strict';

	const directions = ['pg-lytical-from-top', 'pg-lytical-from-bottom', 'pg-lytical-from-left', 'pg-lytical-from-right'];
	const allFrom = directions.join(' ');

	const getDirectionClass = (event) => {
		const el = event.currentTarget;
		const rect = el.getBoundingClientRect();
		const x = event.clientX;
		const y = event.clientY;
		const left = x - rect.left;
		const topDist = y - rect.top;
		const right = rect.right - x;
		const bottom = rect.bottom - y;
		const minDistance = Math.min(left, topDist, right, bottom);

		if (minDistance === topDist) {
			return 'pg-lytical-from-top';
		}

		if (minDistance === bottom) {
			return 'pg-lytical-from-bottom';
		}

		if (minDistance === left) {
			return 'pg-lytical-from-left';
		}

		return 'pg-lytical-from-right';
	};

	const widgetLytical = ($scope) => {
		const $grid = $scope.find('.pg-lytical-grid');
		if (!$grid.length) {
			return;
		}

		$grid
			.off('.pgLyticalDir')
			.on('mouseenter.pgLyticalDir', '.pg-lytical-item', (event) => {
				const el = event.currentTarget;
				const from = getDirectionClass(event);

				$(el).removeClass(allFrom).addClass(from);
			})
			.on('mouseleave.pgLyticalDir', '.pg-lytical-item', (event) => {
				$(event.currentTarget).removeClass(allFrom);
			});
	};

	$(window).on('elementor/frontend/init', () => {
		elementorFrontend.hooks.addAction('frontend/element_ready/pg-lytical.default', widgetLytical);
	});

}(jQuery, window.elementorFrontend));
