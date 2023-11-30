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