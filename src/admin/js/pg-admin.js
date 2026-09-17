jQuery(document).ready(function ($) {

    if (jQuery('.wrap').hasClass('pixel-gallery-dashboard')) {

        // Used / unused widget counters on the Core Widgets filter.
        // The System Status charts and the "Active" counts are drawn by the
        // dashboard's own script once that tab is visible.
        var moduleUsedWidget = jQuery('#pixel_gallery_active_modules_page').find('.pg-used-widget');
        var moduleUsedWidgetCount = jQuery('#pixel_gallery_active_modules_page').find('.pg-options .pg-used').length;
        moduleUsedWidget.text(moduleUsedWidgetCount);
        var moduleUnusedWidget = jQuery('#pixel_gallery_active_modules_page').find('.pg-unused-widget');
        var moduleUnusedWidgetCount = jQuery('#pixel_gallery_active_modules_page').find('.pg-options .pg-unused').length;
        moduleUnusedWidget.text(moduleUnusedWidgetCount);

    }

    jQuery('.pixel-gallery-biggopti.biggopti-error img').css({
        'margin-right': '8px',
        'vertical-align': 'middle'
    });

});
