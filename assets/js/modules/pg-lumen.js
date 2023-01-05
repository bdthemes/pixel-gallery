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