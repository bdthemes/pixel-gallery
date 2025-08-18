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

    /* ===================================
       Admin Store API NOTICE
       =================================== */
    
    /**
     * Initialize countdown timers for API notices
     * This function finds all countdown elements and starts the countdown timer
     */
    function initAPINoticeCountdown() {
        // Find all countdown elements on the page
        jQuery('.bdt-notice-countdown').each(function() {
            var $countdown = jQuery(this);
            var $timer = $countdown.find('.countdown-timer');
            var endDate = $countdown.data('end-date');
            var timezone = $countdown.data('timezone');
            
            // Skip if no end date or timer element found
            if (!endDate || !$timer.length) {
                return;
            }
            
            /**
             * Update the countdown display
             * Calculates time remaining and formats it for display
             */
            function updateCountdown() {
                var endTime = new Date(endDate + ' ' + timezone).getTime();
                var now = new Date().getTime();
                var distance = endTime - now;
                
                // If countdown has expired, hide the countdown
                if (distance < 0) {
                    $countdown.hide();
                    return;
                }
                
                // Calculate time units
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Add leading zeros
                days = days < 10 ? "0" + days : days;
                hours = hours < 10 ? "0" + hours : hours; 
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                
                // Build countdown text with wrapped numbers and labels
                var countdownText = "";
                if (days > 0) {
                    countdownText += '<div class="countdown-item"><span class="number">' + days + '</span><span class="label">days</span></div><span class="separator"></span>';
                }
                // Always show hours (even if 00) for consistent layout
                countdownText += '<div class="countdown-item"><span class="number">' + hours + '</span><span class="label">hrs</span></div><span class="separator"></span>';
                
                countdownText += '<div class="countdown-item"><span class="number">' + minutes + '</span><span class="label">min</span></div><span class="separator"></span>';
                
                countdownText += '<div class="countdown-item"><span class="number">' + seconds + '</span><span class="label">sec</span></div>';
                
                // Update the timer display
                $timer.html(countdownText);
            }
            
            // Initial update to show countdown immediately
            updateCountdown();
            
            // Set up interval to update countdown every second
            setInterval(updateCountdown, 1000);
        });
    }
    
    // Initialize countdown on page load
    initAPINoticeCountdown();
    
    // Re-initialize countdown when new notices are added (for dynamic content)
    // This ensures countdown works even if notices are loaded after page load
    jQuery(document).on('DOMNodeInserted', '.bdt-notice-countdown', function() {
        initAPINoticeCountdown();
    });

    /* ===================================
       END Admin Store API NOTICE
       =================================== */

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

    // Button Color
window.CSS.registerProperty({
    name: '--primaryColor',
    syntax: '<color>',
      inherits: false,
      initialValue: '#AA00FF',
    });
    
    window.CSS.registerProperty({
    name: '--secondaryColor',
    syntax: '<color>',
      inherits: false,
      initialValue: '#FF2661',
});

});