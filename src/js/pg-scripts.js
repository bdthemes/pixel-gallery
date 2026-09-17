var debounce = function(func, wait, immediate) {
    // 'private' variable for instance
    // The returned function will be able to reference this due to closure.
    // Each call to the returned function will share this common timer.
    var timeout;

    // Calling debounce returns a new anonymous function
    return function() {
        // reference the context and args for the setTimeout function
        var context = this,
            args = arguments;

        // Should the function be called now? If immediate is true
        //   and not already in a timeout then the answer is: Yes
        var callNow = immediate && !timeout;

        // This is the basic debounce behaviour where you can call this
        //   function several times, but it will only execute once
        //   [before or after imposing a delay].
        //   Each time the returned function is called, the timer starts over.
        clearTimeout(timeout);

        // Set the new timeout
        timeout = setTimeout(function() {

            // Inside the timeout function, clear the timeout variable
            // which will let the next execution run when in 'immediate' mode
            timeout = null;

            // Check if the function already ran with the immediate flag
            if (!immediate) {
                // Call the original function with apply
                // apply lets you define the 'this' object as well as the arguments
                //    (both captured before setTimeout)
                func.apply(context, args);
            }
        }, wait);

        // Immediate mode and no wait timer? Execute the function..
        if (callNow) func.apply(context, args);
    };
};;(function ($, elementor) {

    'use strict';

    var widgetTurbo = function ($scope, $) {

        var spanText = $scope.find('.pg-turbo-content'),
            gridItem = $scope.find('.pg-turbo-item');

         $(gridItem).on('mousemove', function(e){
            var x = e.clientX,
                y = e.clientY;

            spanText.css('top', (y + 20) + 'px');
            spanText.css('left', (x + 20) + 'px');
        });
    };


    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/pg-turbo.default', widgetTurbo);
    });

}(jQuery, window.elementorFrontend));;(function ($, elementor) {

    'use strict';

    var widgetlumen = function ($scope, $) {

        // Scope to this widget; a document-wide query re-binds every Lumen item on
        // the page each time any Lumen widget becomes ready (or re-renders in the editor).
        var nodes = [].slice.call($scope[0].querySelectorAll('.pg-lumen-item'), 0);
        var directions = { 0: 'top', 1: 'right', 2: 'bottom', 3: 'left' };
        var classNames = ['in', 'out'].map(p => Object.values(directions).map(d => `${p}-${d}`)).reduce((a, b) => a.concat(b));

        var getDirectionKey = (ev, node) => {
        var { width, height, top, left } = node.getBoundingClientRect();
        var l = ev.pageX - (left + window.pageXOffset);
        var t = ev.pageY - (top + window.pageYOffset);
        var x = l - width / 2 * (width > height ? height / width : 1);
        var y = t - height / 2 * (height > width ? width / height : 1);
        return Math.round(Math.atan2(y, x) / 1.57079633 + 5) % 4;
        };

        class Item {
        constructor(element) {
            this.element = element;
            this.element.addEventListener('mouseover', ev => this.update(ev, 'in'));
            this.element.addEventListener('mouseout', ev => this.update(ev, 'out'));
        }

        update(ev, prefix) {
            this.element.classList.remove(...classNames);
            this.element.classList.add(`${prefix}-${directions[getDirectionKey(ev, this.element)]}`);
        }}


        nodes.forEach(node => new Item(node));
    };


    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/pg-lumen.default', widgetlumen);
    });

}(jQuery, window.elementorFrontend));;(function ($, elementor) {
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
