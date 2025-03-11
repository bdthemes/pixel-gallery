(function ($, elementor) {
	"use strict";

	function pgObserveTarget(target, callback) {
		var options =
			arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
		// Set the rootMargin to trigger when the target is 10% past the viewport
		options.rootMargin = options.rootMargin || "10% 0px 0px 0px";
		var observer = new IntersectionObserver(function (entries, observer) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
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

		var itemQueue = [];
		var delay = $animations.data("in-animation-delay")
			? $animations.data("in-animation-delay")
			: 200;
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

		pgObserveTarget(
			$($animations[0]).find(".pg-item")[0],
			function () {
				itemQueue.push($($animations[0]).find(".pg-item"));
				processItemQueue();
			},
			{
				root: null,
				rootMargin: "0px",
				threshold: 0.8,
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
