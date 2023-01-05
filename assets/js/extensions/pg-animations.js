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


