<?php
/**
 * Pixel Gallery Others Plugin - Standalone Plugin Manager
 * 
 * This file provides the enhanced plugin installation and management system
 * for Pixel Gallery, separated from the main admin settings for better maintainability.
 * 
 * @version 1.0.0
 * @author BDThemes
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pixel Gallery Others Plugin Manager
 */
class PixelGallery_Others_Plugin_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        // Add AJAX handlers
        add_action('wp_ajax_bdtpg_get_plugins', [$this, 'ajax_get_plugins']);
        add_action('wp_ajax_bdtpg_install_plugin', [$this, 'install_plugin_ajax']);
    }

    /**
     * Render the others plugin interface
     */
    public function render_others_plugin() {
        // Include the required classes
        require_once BDTPG_INC_PATH . 'setup-wizard/class-plugin-integration-helper.php';
        require_once BDTPG_INC_PATH . 'setup-wizard/class-remote-data-handler.php';
        
        // Define plugin slugs for reference
        $plugin_slugs = array(
            'bdthemes-prime-slider-lite/bdthemes-prime-slider.php',
            'ultimate-post-kit',
            'ultimate-store-kit',
            'zoloblocks',
            'pixel-gallery',
            'live-copy-paste',
            'spin-wheel',
            'ai-image',
            'dark-reader',
            'ar-viewer',
            'smart-admin-assistant',
            'website-accessibility',
        );

        // Helper function for time formatting
        if (!function_exists('pixel_gallery_format_last_updated')) {
            function pixel_gallery_format_last_updated($date_string) {
                if (empty($date_string)) {
                    return __('Unknown', 'pixel-gallery');
                }
                
                $date = strtotime($date_string);
                if (!$date) {
                    return __('Unknown', 'pixel-gallery');
                }
                
                $diff = current_time('timestamp') - $date;
                
                if ($diff < 60) {
                    return __('Just now', 'pixel-gallery');
                } elseif ($diff < 3600) {
                    $minutes = floor($diff / 60);
                    /* translators: %d: number of minutes (the count). */
                    return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'pixel-gallery'), $minutes);
                } elseif ($diff < 86400) {
                    $hours = floor($diff / 3600);
                    /* translators: %d: number of hours (the count). */
                    return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'pixel-gallery'), $hours);
                } elseif ($diff < 2592000) { // 30 days
                    $days = floor($diff / 86400);
                    /* translators: %d: number of days (the count). */
                    return sprintf(_n('%d day ago', '%d days ago', $days, 'pixel-gallery'), $days);
                } elseif ($diff < 31536000) { // 1 year
                    $months = floor($diff / 2592000);
                    /* translators: %d: number of months (the count). */
                    return sprintf(_n('%d month ago', '%d months ago', $months, 'pixel-gallery'), $months);
                } else {
                    $years = floor($diff / 31536000);
                    /* translators: %d: number of years (the count). */
                    return sprintf(_n('%d year ago', '%d years ago', $years, 'pixel-gallery'), $years);
                }
            }
        }

        ?>
        
        <div class="pg-dashboard-panel"
            bdt-scrollspy="target: > div > div > .bdt-card; cls: bdt-animation-slide-bottom-small; delay: 300">
            <div class="pg-dashboard-others-plugin" id="pg-others-plugin-container">
                
                <!-- Loading state -->
                <div class="pg-plugins-loading" id="pg-plugins-loading">
                    <div class="bdt-flex bdt-flex-center bdt-flex-middle bdt-text-center" style="min-height: 200px;">
                        <div>
                            <div class="bdt-spinner bdt-spinner-primary"></div>
                            <p class="bdt-margin-small-top"><?php esc_html_e('Loading plugin data...', 'pixel-gallery'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Error state (hidden by default) -->
                <div class="pg-plugins-error" id="pg-plugins-error" style="display: none;">
                    <div class="bdt-alert bdt-alert-warning" bdt-alert>
                        <a class="bdt-alert-close" bdt-close></a>
                        <p><?php esc_html_e('Unable to load plugin data. Please try again later.', 'pixel-gallery'); ?></p>
                        <button class="bdt-button bdt-button-small bdt-margin-small-top" id="pg-retry-load-plugins">
                            <?php esc_html_e('Retry', 'pixel-gallery'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Plugins container (populated by AJAX) -->
                <div class="pg-plugins-list" id="pg-plugins-list" style="display: none;">
                    <!-- Plugin cards will be inserted here by JavaScript -->
                </div>
            </div>
        </div>
        
        <style type="text/css">
        .pg-loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        .pg-loading-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .pg-loading-dot {
            width: 12px;
            height: 12px;
            background-color: #086698;
            border-radius: 50%;
            animation: pg-wave 1.4s ease-in-out infinite both;
        }
        
        .pg-loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .pg-loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .pg-loading-dot:nth-child(3) { animation-delay: 0; }
        
        @keyframes pg-wave {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }
        
        #pg-plugins-list {
            position: relative;
            min-height: 200px;
        }

        #pg-plugins-list p {
            max-inline-size: none;
            margin-top: 60px !important;
        }

        </style>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $container = $('#pg-others-plugin-container');
            var $loading = $('#pg-plugins-loading');
            var $error = $('#pg-plugins-error');
            var $list = $('#pg-plugins-list');
            
            // Function to load plugins via AJAX
            function loadPlugins() {
                $loading.hide();
                $error.hide();
                showLoading();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bdtpg_get_plugins',
                        nonce: '<?php echo esc_attr( wp_create_nonce("bdtpg_get_plugins_nonce") ); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            if (response.data.loading) {
                                // Still loading, show message and retry after delay
                                showLoading();
                                setTimeout(loadPlugins, 3000); // Retry after 3 seconds
                            } else {
                                renderPlugins(response.data.plugins);
                            }
                        } else {
                            showError();
                        }
                    },
                    error: function() {
                        showError();
                    }
                });
            }
            
            // Function to render plugins
            function renderPlugins(plugins) {
                var html = '';
                
                if (plugins.length === 0) {
                    html = '<div class="bdt-text-center bdt-padding-large"><p><?php esc_html_e('No plugins available.', 'pixel-gallery'); ?></p></div>';
                } else {
                    plugins.forEach(function(plugin) {
                        // Skip own plugin (Pixel Gallery) when printing only; data still includes it for other plugins
                        if (plugin.slug === 'pixel-gallery') return;
                        var isActive = false; // We'll determine this via PHP in the actual implementation
                        var pluginName = plugin.name || '';
                        var pluginSlug = plugin.slug || '';
                        // Icons are rendered locally from the plugin name; nothing is
                        // loaded from a remote server.
                        var pluginInitial = pluginName.replace(/<[^>]*>/g, '').trim().charAt(0).toUpperCase() || '#';
                        pluginInitial = pluginInitial.replace(/[&<>"']/g, function (c) {
                            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                        });
                        
                        html += '<div class="bdt-card bdt-card-body bdt-flex bdt-flex-middle bdt-flex-between">' +
                            '<div class="bdt-others-plugin-content">' +
                                '<div class="bdt-plugin-logo-wrap bdt-flex bdt-flex-middle">' +
                                    '<div class="bdt-plugin-logo-container">' +
                                        '<div class="default-plugin-icon" aria-hidden="true">' + pluginInitial + '</div>' +
                                    '</div>' +
                                    '<div class="bdt-others-plugin-user-wrap bdt-flex bdt-flex-middle">' +
                                        '<h1 class="pg-feature-title">' + pluginName + '</h1>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="bdt-others-plugin-content-text bdt-margin-top">';
                        
                        if (plugin.description) {
                            html += '<p>' + plugin.description + '</p>';
                        }
                        
                        // Active installs
                        html += '<span class="active-installs bdt-margin-small-top">' +
                            '<?php esc_html_e("Active Installs: ", "pixel-gallery"); ?> ';
                        if (plugin.active_installs_count > 0) {
                            html += '<span class="installs-count">' + plugin.active_installs_count.toLocaleString() + '+</span>';
                        } else {
                            html += '<span class="installs-count">Fewer than 10</span>';
                        }
                        html += '</span>';
                        
                        // Rating
                        html += '<div class="bdt-others-plugin-rating bdt-margin-small-top bdt-flex bdt-flex-middle">' +
                            '<span class="bdt-others-plugin-rating-stars">';
                        
                        var rating = parseFloat(plugin.rating) || 0;
                        var fullStars = Math.floor(rating);
                        var hasHalfStar = (rating - fullStars) >= 0.5;
                        var emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
                        
                        for (var i = 0; i < fullStars; i++) {
                            html += '<i class="dashicons dashicons-star-filled"></i>';
                        }
                        if (hasHalfStar) {
                            html += '<i class="dashicons dashicons-star-half"></i>';
                        }
                        for (var i = 0; i < emptyStars; i++) {
                            html += '<i class="dashicons dashicons-star-empty"></i>';
                        }
                        
                        html += '</span>' +
                            '<span class="bdt-others-plugin-rating-text bdt-margin-small-left">' +
                                rating + ' <?php esc_html_e("out of 5 stars.", "pixel-gallery"); ?>';
                        
                        if (plugin.num_ratings > 0) {
                            html += '<span class="rating-count">(' + plugin.num_ratings.toLocaleString() + ' <?php esc_html_e("ratings", "pixel-gallery"); ?>)</span>';
                        }
                        
                        html += '</span></div>';
                        
                        // Downloads
                        if (plugin.downloaded_formatted) {
                            html += '<div class="bdt-others-plugin-downloads bdt-margin-small-top">' +
                                '<span><?php esc_html_e("Downloads: ", "pixel-gallery"); ?>' + plugin.downloaded_formatted + '</span>' +
                                '</div>';
                        }
                        
                        // Last updated
                        if (plugin.last_updated_formatted) {
                            html += '<div class="bdt-others-plugin-updated bdt-margin-small-top">' +
                                '<span><?php esc_html_e("Last Updated: ", "pixel-gallery"); ?>' + plugin.last_updated_formatted + '</span>' +
                                '</div>';
                        }
                        
                        html += '</div></div>' +
                            '<div class="bdt-others-plugins-link">';
                        
                        // Show different buttons based on plugin status
                        if (plugin.status === 'active') {
                            html += '<span class="bdt-button bdt-button-success bdt-disabled">' +
                                '<span class="dashicons dashicons-yes"></span> ' +
                                '<?php esc_html_e("Active", "pixel-gallery"); ?>' +
                                '</span>';
                        } else if (plugin.status === 'installed') {
                            var activateUrl = '<?php echo esc_url( admin_url("plugins.php?action=activate&plugin=") ); ?>' + plugin.plugin_file + '&_wpnonce=' + plugin.activate_nonce;
                            html += '<a class="bdt-button bdt-welcome-button" href="' + activateUrl + '">' +
                                '<?php esc_html_e("Activate", "pixel-gallery"); ?>' +
                                '</a>';
                        } else {
                            html += '<button class="bdt-button bdt-welcome-button pg-install-plugin" data-plugin-slug="' + pluginSlug + '" data-nonce="<?php echo esc_attr( wp_create_nonce('bdtpg_install_plugin_nonce') ); ?>">' +
                                '<?php esc_html_e("Install", "pixel-gallery"); ?>' +
                                '</button>';
                        }
                        
                        if (plugin.homepage) {
                            html += '<a class="bdt-button bdt-dashboard-sec-btn" target="_blank" href="' + plugin.homepage + '">' +
                                '<?php esc_html_e("Learn More", "pixel-gallery"); ?>' +
                                '</a>';
                        }
                        
                        html += '</div></div>';
                    });
                }
                
                $list.html(html);
                
                // Handle plugin action buttons
                $('.pg-install-plugin').on('click', function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var pluginSlug = $button.data('plugin-slug');
                    var nonce = $button.data('nonce');
                    var originalText = $button.text();
                    
                    // Disable button and show loading state
                    $button.prop('disabled', true)
                           .text('<?php echo esc_js(__('Installing...', 'pixel-gallery')); ?>')
                           .addClass('bdt-installing');
                    
                    // Perform AJAX request
                    $.ajax({
                        url: '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>',
                        type: 'POST',
                        data: {
                            action: 'bdtpg_install_plugin',
                            plugin_slug: pluginSlug,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                $button.text('<?php echo esc_js(__('Installed!', 'pixel-gallery')); ?>')
                                       .removeClass('bdt-installing')
                                       .addClass('bdt-installed');
                                
                                // Show success notification
                                if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
                                    bdtUIkit.notification({
                                        message: '<span class="dashicons dashicons-yes"></span> ' + response.data.message,
                                        status: 'success'
                                    });
                                }
                                
                                // Reload the page after 2 seconds to update button states
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                                
                            } else {
                                // Show error message
                                $button.prop('disabled', false)
                                       .text(originalText)
                                       .removeClass('bdt-installing');
                                
                                // Show error notification
                                if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
                                    bdtUIkit.notification({
                                        message: '<span class="dashicons dashicons-warning"></span> ' + response.data.message,
                                        status: 'danger'
                                    });
                                } else {
                                    alert('Error: ' + response.data.message);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            // Show error message
                            $button.prop('disabled', false)
                                   .text(originalText)
                                   .removeClass('bdt-installing');
                            
                            // Show error notification
                            if (typeof bdtUIkit !== 'undefined' && bdtUIkit.notification) {
                                bdtUIkit.notification({
                                    message: '<span class="dashicons dashicons-warning"></span> <?php echo esc_js(__('Installation failed. Please try again.', 'pixel-gallery')); ?>',
                                    status: 'danger'
                                });
                            } else {
                                alert('<?php echo esc_js(__('Installation failed. Please try again.', 'pixel-gallery')); ?>');
                            }
                        }
                    });
                });
            }
            
            // Function to show loading state
            function showLoading() {
                $list.html(
                    '<div class="bdt-text-center bdt-padding-large">' +
                        '<div class="pg-loading-spinner">' +
                            '<div class="pg-loading-dots">' +
                                '<div class="pg-loading-dot"></div>' +
                                '<div class="pg-loading-dot"></div>' +
                                '<div class="pg-loading-dot"></div>' +
                            '</div>' +
                        '</div>' +
                        '<p class="bdt-margin-small-top bdt-text-muted"><?php esc_html_e("Loading plugin data...", "pixel-gallery"); ?></p>' +
                    '</div>'
                );
                $list.show();
            }
            
            // Function to show error
            function showError() {
                $error.show();
                $list.hide();
            }
            
            // Retry button handler
            $('#pg-retry-load-plugins').on('click', function() {
                loadPlugins();
            });
            
            // Initial load
            loadPlugins();
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for getting plugins data
     */
    public function ajax_get_plugins() {
        // Verify nonce
        if (!check_ajax_referer('bdtpg_get_plugins_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => __('Security check failed.', 'pixel-gallery')], 403);
        }

        // Only users who could act on this list are allowed to request it.
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to view this list.', 'pixel-gallery')], 403);
        }

        // Get cached data
        $plugins_data = \PixelGallery\SetupWizard\Remote_Data_Handler::get_remote_plugins();
        
        // If cache is empty, try to fetch immediately (but don't block)
        if (empty($plugins_data)) {
            // Schedule background fetch if not already done
            \PixelGallery\SetupWizard\Remote_Data_Handler::schedule_remote_fetch();
            
            // Return empty response with flag indicating data is loading
            wp_send_json_success([
                'plugins' => [],
                'loading' => true,
                'message' => __('Loading plugin data...', 'pixel-gallery')
            ]);
        }

        // Send response
        wp_send_json_success([
            'plugins' => $plugins_data,
            'loading' => false,
            'message' => __('Plugin data loaded successfully.', 'pixel-gallery')
        ]);
    }

    /**
     * AJAX handler for plugin installation
     */
    public function install_plugin_ajax() {
        // Check nonce
        if (!wp_verify_nonce(isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '', 'bdtpg_install_plugin_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'pixel-gallery')]);
        }

        // Check user capability
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to install plugins', 'pixel-gallery')]);
        }

        $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';

        if (empty($plugin_slug)) {
            wp_send_json_error(['message' => __('Plugin slug is required', 'pixel-gallery')]);
        }

        // Include necessary WordPress files
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

        // Get plugin information
        $api = plugins_api('plugin_information', [
            'slug' => $plugin_slug,
            'fields' => [
                'sections' => false,
            ],
        ]);

        if (is_wp_error($api)) {
            wp_send_json_error(['message' => __('Plugin not found: ', 'pixel-gallery') . $api->get_error_message()]);
        }

        // Install the plugin
        $skin = new \WP_Ajax_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader($skin);
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Installation failed: ', 'pixel-gallery') . $result->get_error_message()]);
        } elseif ($skin->get_errors()->has_errors()) {
            wp_send_json_error(['message' => __('Installation failed: ', 'pixel-gallery') . $skin->get_error_messages()]);
        } elseif (is_null($result)) {
            wp_send_json_error(['message' => __('Installation failed: Unable to connect to filesystem', 'pixel-gallery')]);
        }

        // Get installation status
        $install_status = install_plugin_install_status($api);
        
        wp_send_json_success([
            'message' => __('Plugin installed successfully!', 'pixel-gallery'),
            'plugin_file' => $install_status['file'],
            'plugin_name' => $api->name
        ]);
    }
}

// Initialize the manager
new PixelGallery_Others_Plugin_Manager();

/**
 * Helper function for easy rendering
 */
function pixel_gallery_others_plugin() {
    $manager = new PixelGallery_Others_Plugin_Manager();
    $manager->render_others_plugin();
}
