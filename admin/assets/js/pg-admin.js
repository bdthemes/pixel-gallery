jQuery(document).ready(function ($) {

    jQuery('.pixel-gallery-notice.is-dismissible .notice-dismiss').on('click', function () {
        $this = jQuery(this).parents('.pixel-gallery-notice');
        var $id = $this.attr('id') || '';
        var $time = $this.attr('dismissible-time') || '';
        var $meta = $this.attr('dismissible-meta') || '';

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pixel-gallery-notices',
                id: $id,
                meta: $meta,
                time: $time
            }
        });

    });

    if (jQuery('.wrap').hasClass('pixel-gallery-dashboard')) {


        // total activate
        function total_widget_status() {
            var total_widget_active_status = [];

            var totalActivatedWidgets = [];
            var totalWidgets = [];
            jQuery('#pixel_gallery_active_modules_page input:checked').each(function () {
                totalActivatedWidgets.push(jQuery(this).attr('name'));
            });

            jQuery('#pixel_gallery_active_modules_page .bdt-width-auto input:checkbox').each(function () {
                totalWidgets.push(jQuery(this).attr('name'));
            });

            total_widget_active_status.push(totalActivatedWidgets.length);
            total_widget_active_status.push(totalWidgets.length - totalActivatedWidgets.length);

            jQuery('#bdt-total-widgets-status').attr('data-value', total_widget_active_status);
            jQuery('#bdt-total-widgets-status-core').text(totalActivatedWidgets.length);

            jQuery('#bdt-total-widgets-status-heading').text(totalWidgets.length);

        }

        total_widget_status();

        jQuery('.pixel-gallery-settings-save-btn').on('click', function () {
            setTimeout(function () {
                total_widget_status();
            }, 2000);
        });

        // end total active



        // modules
        var moduleUsedWidget = jQuery('#pixel_gallery_active_modules_page').find('.pg-used-widget');
        var moduleUsedWidgetCount = jQuery('#pixel_gallery_active_modules_page').find('.pg-options .pg-used').length;
        moduleUsedWidget.text(moduleUsedWidgetCount);
        var moduleUnusedWidget = jQuery('#pixel_gallery_active_modules_page').find('.pg-unused-widget');
        var moduleUnusedWidgetCount = jQuery('#pixel_gallery_active_modules_page').find('.pg-options .pg-unused').length;
        moduleUnusedWidget.text(moduleUnusedWidgetCount);


        // total widgets 

        var dashboardChatItems = ['#bdt-db-total-status', '#bdt-total-widgets-status'];

        dashboardChatItems.forEach(function ($el) {

            const ctx = jQuery($el);

            var $value = ctx.data('value');
            $value = $value.split(',');

            var $labels = ctx.data('labels');
            $labels = $labels.split(',');

            var $bg = ctx.data('bg');
            $bg = $bg.split(',');

            const data = {
                // labels: $labels,
                datasets: [{
                    data: $value,
                    backgroundColor: $bg,
                    borderWidth: 0,
                }],

            };

            const config = {
                type: 'doughnut',
                data: data,
                options: {
                    animation: {
                        duration: 3000,
                    },

                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    title: {
                        display: false,
                        text: ctx.data('label'),
                        fontSize: 16,
                        fontColor: '#333',
                    },
                    hover: {
                        mode: null
                    },

                }
            };

            if (window.myChart instanceof Chart) {
                window.myChart.destroy();
            }

            var myChart = new Chart(ctx, config);

        });

    }

    jQuery('.pixel-gallery-notice.notice-error img').css({
        'margin-right': '8px',
        'vertical-align': 'middle'
    });

});