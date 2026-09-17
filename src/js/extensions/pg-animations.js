(function ($, elementor) {
	"use strict";

	function pgObserveTarget(target, callback) {
		var options =
			arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
		// Set the rootMargin to trigger when the target is 10% past the viewport
		options.rootMargin = options.rootMargin || "10% 0px 0px 0px";
		var isReady =
			options.isReady ||
			function (entry) {
				return entry.isIntersecting;
			};
		var observer = new IntersectionObserver(function (entries, observer) {
			entries.forEach(function (entry) {
				if (isReady(entry)) {
					callback(entry);

					if (!options.loop) observer.unobserve(entry.target); // Unobserve after the first intersection
				}
			});
		}, options);
		observer.observe(target);
	}

	var extensionAnimations = function ($scope, $) {
		var $animations = $scope.find(".pg-in-animation");

		if (!$animations.length) {
			return;
		}

		var $items = $($animations[0]).find(".pg-item");

		if (!$items.length) {
			return;
		}

		var itemQueue = [];
		var delayData = $animations.data("in-animation-delay");
		// An explicit 0 means no delay; only a missing value falls back to 200ms.
		var delay =
			undefined === delayData || "" === delayData
				? 200
				: parseInt(delayData, 10) || 0;
		var queueTimer;

		function processItemQueue() {
			if (queueTimer) return; // We're already processing the queue

			queueTimer = window.setInterval(function () {
				if (itemQueue.length) {
					jQuery(itemQueue.shift()).addClass("is-inview");
					processItemQueue();
				} else {
					window.clearInterval(queueTimer);
					queueTimer = null;
				}
			}, delay);
		}

		var thresholds = [];
		for (var i = 0; i <= 20; i++) {
			thresholds.push(i / 20);
		}

		pgObserveTarget(
			$items[0],
			function () {
				itemQueue.push($items);
				processItemQueue();
			},
			{
				root: null,
				rootMargin: "0px",
				threshold: thresholds,
				// Reveal when 80% of the first item is visible. An item taller than
				// the viewport can never reach that ratio, so also reveal it once it
				// fills 80% of the viewport; otherwise the gallery stays invisible.
				isReady: function (entry) {
					if (!entry.isIntersecting) {
						return false;
					}

					var viewportHeight = entry.rootBounds
						? entry.rootBounds.height
						: window.innerHeight;

					return (
						entry.intersectionRatio >= 0.8 ||
						entry.intersectionRect.height >= viewportHeight * 0.8
					);
				},
			}
		);
	};

	jQuery(window).on("elementor/frontend/init", function () {
		var $widgets = [
			"alien",
			"aware",
			"axen",
			"craze",
			"crop",
			"doodle",
			"elixir",
			"epoch",
			"fabric",
			"fever",
			"fixer",
			"flame",
			"fluid",
			"glam",
			"glaze",
			"humble",
			"insta",
			"koral",
			"lumen",
			"lunar",
			"lytical",
			"marron",
			"mastery",
			"mosaic",
			"mystic",
			"nexus",
			"ocean",
			"orbit",
			"panda",
			"plex",
			"plumb",
			"punch",
			"ranch",
			"remix",
			"ruby",
			"shark",
			"sonic",
			"spirit",
			"tour",
			"trance",
			// 'turbo',
			"verse",
			"walden",
			"wisdom",
			"zilax",
			// 'heron',
			"maven",
		];

		$.each($widgets, function (index, value) {
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/pg-" + value + ".default",
				extensionAnimations
			);
		});
	});
})(jQuery, window.elementorFrontend);
