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
};
(function ($, elementor) {

    'use strict';

    var widgetTurbo = function ($scope, $) {

        var spanText = $scope.find('.pg-turbo-content'),
            gridItem = $scope.find('.pg-turbo-item');

         $(gridItem).mousemove(function(e){
            var x = e.clientX,
                y = e.clientY;

            spanText.css('top', (y + 20) + 'px');
            spanText.css('left', (x + 20) + 'px');
        });
    };


    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/pg-turbo.default', widgetTurbo);
    });

}(jQuery, window.elementorFrontend));
(function ($, elementor) {

    'use strict';

    var widgetlumen = function ($scope, $) {

        var nodes = [].slice.call(document.querySelectorAll('.pg-lumen-item'), 0);
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

}(jQuery, window.elementorFrontend));
(function ($, elementor) {

    'use strict';

    var extensionAnimations = function ($scope, $) {

        var $animations = $scope.find('.pg-in-animation');

        if (!$animations.length) {
            return;
        }

        var itemQueue = [];
        var delay = ($animations.data('in-animation-delay')) ? $animations.data('in-animation-delay') : 200;
        var queueTimer;

        function processItemQueue() {
            if (queueTimer) return // We're already processing the queue

            queueTimer = window.setInterval(function () {
                if (itemQueue.length) {
                    jQuery(itemQueue.shift()).addClass('is-inview');
                    processItemQueue();
                } else {
                    window.clearInterval(queueTimer)
                    queueTimer = null
                }
            }, delay)
        }

        elementorFrontend.waypoint(jQuery('.pg-in-animation .pg-item'), function () {
            itemQueue.push($(this));
            processItemQueue();
        }, {
            offset: '90%'
        });

    };

    jQuery(window).on('elementor/frontend/init', function () {

        var $widgets = [ 
            'alien',
			'aware',
			'axen',
			'craze',
			'crop',
			'doodle',
			'elixir',
			'epoch',
			'fabric',
			'fever',
			'fixer',
			'flame',
			'fluid',
			'glam',
			'glaze',
			'humble',
			'insta',
			'koral',
			'lumen',
			'lunar',
			'lytical',
			'marron',
			'mastery',
			'mosaic',
			'mystic',
			'nexus',
			'ocean',
			'orbit',
			'panda',
			'plex',
			'plumb',
			'punch',
			'ranch',
			'remix',
			'ruby',
			'shark',
			'sonic',
			'spirit',
			'tour',
			'trance',
			// 'turbo',
			'verse',
			'walden',
			'wisdom',
			'zilax',
			// 'heron',
			'maven'
        ];

        $.each($widgets, function(index, value) {
            elementorFrontend.hooks.addAction('frontend/element_ready/pg-' + value +'.default', extensionAnimations);
        });
    });

}(jQuery, window.elementorFrontend));


