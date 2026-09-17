<?php
/**
 * Integration Step
 */

namespace PixelGallery\SetupWizard;

if (!defined('ABSPATH')) {
    exit;
}

// Include the required classes
require_once __DIR__ . '/../class-plugin-integration-helper.php';
require_once __DIR__ . '/../class-remote-data-handler.php';

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

// Build a local, offline initial for a plugin so no icon has to be fetched
// from a remote server (WordPress.org disallows offloading assets).
if (!function_exists(__NAMESPACE__ . '\\pixel_gallery_plugin_icon_initial')) {
    function pixel_gallery_plugin_icon_initial($plugin_name) {
        $plugin_name = trim(wp_strip_all_tags((string) $plugin_name));

        if ('' === $plugin_name) {
            return '#';
        }

        return strtoupper(mb_substr($plugin_name, 0, 1));
    }
}

/**
 * Render the "Integration" step of the setup wizard.
 *
 * Wrapped in a function so none of its working variables land in the global scope.
 */
function pixel_gallery_render_integration_step() {

// Get enhanced plugin data using the remote data handler
$pg_plugins = Remote_Data_Handler::get_remote_plugins();

// Check if we have cached data
$has_cached_data = !empty($pg_plugins);

// If no cached data, don't fetch immediately - let JavaScript handle it
if (!$has_cached_data) {
    $pg_plugins = []; // Empty array for initial load
}
?>

<div class="bdt-wizard-step bdt-setup-wizard-integration" data-step="integration">
    <h2><?php esc_html_e('Add More Firepower', 'pixel-gallery'); ?></h2>
    <p><?php esc_html_e('You can onboard additional powerful plugins to extend your web design capabilities.', 'pixel-gallery'); ?></p>

    <div class="pg-progress-bar-container">
        <div id="plugin-install-progress" class="pg-progress-bar"></div>
    </div>

    <form method="POST" id="pg-install-plugins">
        <!-- Loading state - shown during plugin installation -->
        <div class="pg-loading-state" id="pg-install-loading" style="display: none; text-align: center; padding: 40px;">
            <div class="pg-loading-dots">
                <div class="pg-loading-dot"></div>
                <div class="pg-loading-dot"></div>
                <div class="pg-loading-dot"></div>
            </div>
            <p style="margin-top: 20px;" id="pg-loading-message"><?php esc_html_e('Installing plugins...', 'pixel-gallery'); ?></p>
        </div>

        <!-- Initial loading state - shown while fetching plugin data -->
        <?php if (!$has_cached_data): ?>
        <div class="pg-loading-state" id="pg-initial-loading" style="text-align: center; padding: 40px;">
            <div class="pg-loading-dots">
                <div class="pg-loading-dot"></div>
                <div class="pg-loading-dot"></div>
                <div class="pg-loading-dot"></div>
            </div>
            <p style="margin-top: 20px;"><?php esc_html_e('Loading plugin data...', 'pixel-gallery'); ?></p>
        </div>
        <?php endif; ?>

        <div class="bdt-plugin-list" id="pg-integration-plugin-list">
            <?php if ($has_cached_data): ?>
                <?php
                $predefined = \PixelGallery\SetupWizard\Plugin_Integration_Helper::get_predefined_plugins();
                $recommended_by_slug = [];
                foreach ($predefined as $key => $config) {
                    $dir = (strpos($key, '/') !== false) ? dirname($key) : $key;
                    $recommended_by_slug[ $dir ] = !empty($config['recommended']);
                }
                foreach ($pg_plugins as $slug_key => $plugin) :
                    // Skip own plugin (Pixel Gallery) when printing only; data still includes it for other plugins
                    if ($slug_key === 'pixel-gallery') continue;
                    $is_active = 'active' === Remote_Data_Handler::get_plugin_status_by_slug($slug_key);
                    $plugin_recommended = !empty($recommended_by_slug[ $slug_key ]);
                    $is_recommended = $plugin_recommended && !$is_active;
                    $plugin_logo = Remote_Data_Handler::get_local_plugin_logo($slug_key);
                ?>
                    <label class="pg-plugin-item" data-slug="<?php echo esc_attr($plugin['slug']); ?>">
                        <span class="bdt-flex bdt-flex-middle bdt-flex-between bdt-margin-small-bottom">
                            <span class="bdt-plugin-logo">
                                <?php if ($plugin_logo) : ?>
                                    <img src="<?php echo esc_url($plugin_logo); ?>" alt="" width="48" height="48">
                                <?php else : ?>
                                    <div class="pg-default-plugin-icon" aria-hidden="true"><?php echo esc_html(pixel_gallery_plugin_icon_initial($plugin['name'] ?? '')); ?></div>
                                <?php endif; ?>
                            </span>

                            <div class="bdt-plugin-badge-switch-wrap">

                            <?php if ($is_recommended) : ?>
                                <span class="pg-recommended-badge"><?php esc_html_e('Recommended', 'pixel-gallery'); ?></span>
                            <?php endif; ?>

                            <?php if ($is_active) : ?>
                                <span class="pg-active-badge"><?php esc_html_e('ACTIVE', 'pixel-gallery'); ?></span>
                            <?php endif; ?>
                             <?php
                             if (!$is_active) : ?>
                                 <label class="pg-switch">
                                     <input type="checkbox" class="pg-plugin-slider-checkbox"
                                            name="plugins[]" value="<?php echo isset($plugin['slug']) ? esc_attr($plugin['slug']) : ''; ?>">
                                     <span class="pg-slider pg-round"></span>
                                 </label>
                             <?php
                             endif;
                             ?>
                            </div>
                        </span>
                        <div class="bdt-flex bdt-flex-middle">
                                <span class="bdt-plugin-name">
                                    <?php echo esc_html($plugin['name']); ?>
                                </span>
                            </div>

                        <span class="pg-active-installs">
                            <?php esc_html_e('Active Installs: ', 'pixel-gallery');
                            if (isset($plugin['active_installs_count']) && $plugin['active_installs_count'] > 0) {
                                echo ' <span class="pg-installs-count">' . esc_html(number_format($plugin['active_installs_count'])) . '+</span>';
                            } else {
                                echo '<span class="pg-installs-count">Fewer than 10</span>';
                            }
                            ?>
                        </span>

                        <?php if (isset($plugin['downloaded_formatted']) && !empty($plugin['downloaded_formatted'])): ?>
                        <span class="pg-downloads"><?php esc_html_e('Downloads: ', 'pixel-gallery'); echo esc_html($plugin['downloaded_formatted']); ?></span>
                        <?php endif; ?>

                        <div class="pg-rating-section">
                            <div class="pg-wporg-ratings" title="<?php echo esc_attr($plugin['rating'] ?? '0'); ?> out of 5 stars" style="color:var(--wp--preset--color--pomegrade-1, #e26f56);">
                                <?php 
                                $rating = floatval($plugin['rating'] ?? 0);
                                $full_stars = floor($rating);
                                $has_half_star = ($rating - $full_stars) >= 0.5;
                                $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
                                
                                // Full stars
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<span class="dashicons dashicons-star-filled"></span>';
                                }
                                
                                // Half star
                                if ($has_half_star) {
                                    echo '<span class="dashicons dashicons-star-half"></span>';
                                }
                                
                                // Empty stars
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<span class="dashicons dashicons-star-empty"></span>';
                                }
                                ?>
                            </div>
                            <span class="pg-rating-text">
                                <?php echo esc_html($plugin['rating'] ?? '0'); ?> out of 5 stars.
                                <?php if (isset($plugin['num_ratings']) && $plugin['num_ratings'] > 0): ?>
                                    <span class="pg-rating-count">(<?php echo esc_html(number_format($plugin['num_ratings'])); ?> ratings)</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php
                        // Use the enhanced last_updated_formatted if available, otherwise fall back to formatting
                        if (isset($plugin['last_updated_formatted']) && !empty($plugin['last_updated_formatted'])): ?>
                        <span class="pg-last-updated"><?php esc_html_e('Last Updated: ', 'pixel-gallery'); echo esc_html($plugin['last_updated_formatted']); ?></span>
                        <?php elseif (isset($plugin['last_updated']) && !empty($plugin['last_updated'])): ?>
                        <span class="pg-last-updated"><?php esc_html_e('Last Updated: ', 'pixel-gallery'); echo esc_html(pixel_gallery_format_last_updated($plugin['last_updated'])); ?></span>
                        <?php endif; ?>

                    </label>
                <?php
                endforeach; ?>
            <?php endif; ?>
        </div>
        
        <p class="bdt-plugin-consent-note">
            <?php esc_html_e('Nothing is selected by default. Pixel Gallery only installs the plugins you switch on here, and never activates a plugin unless you also tick the box below.', 'pixel-gallery'); ?>
        </p>

        <div class="bdt-plugin-activate-consent pg-d-none" id="pg-activate-consent-wrap">
            <label for="pg-activate-after-install">
                <input type="checkbox" id="pg-activate-after-install" name="pg_activate_after_install" value="1">
                <?php esc_html_e('Also activate the selected plugins after installing them', 'pixel-gallery'); ?>
            </label>
            <span class="bdt-plugin-consent-hint">
                <?php esc_html_e('Leave this unticked to only download the plugins; you can then activate them yourself from the Plugins screen.', 'pixel-gallery'); ?>
            </span>
        </div>

        <div class="pg-wizard-navigation bdt-margin-top">
            <button class="bdt-button bdt-button-primary pg-d-none" type="submit" id="pg-install-plugins-btn">
                <?php esc_html_e('Install Selected Plugins', 'pixel-gallery'); ?>
            </button>
            <button type="button" class="bdt-close-button bdt-margin-left bdt-wizard-next" data-step="finish"><?php esc_html_e('Skip', 'pixel-gallery'); ?></button>
        </div>
    </form>

    <div class="bdt-wizard-navigation">
        <button class="bdt-button bdt-button-secondary bdt-wizard-prev" data-step="features">
            <span><i class="dashicons dashicons-arrow-left-alt"></i></span>
            <?php esc_html_e('Previous Step', 'pixel-gallery'); ?>
        </button>
    </div>
</div>
<?php
} // end pixel_gallery_render_integration_step()

pixel_gallery_render_integration_step();
?>

<style>
.bdt-plugin-consent-note {
    margin: 16px 0 0;
    font-size: 13px;
    line-height: 1.5;
    opacity: 0.8;
}

.bdt-plugin-activate-consent {
    margin-top: 10px;
    font-size: 13px;
    line-height: 1.5;
}

.bdt-plugin-activate-consent label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

.bdt-plugin-consent-hint {
    display: block;
    margin-top: 4px;
    opacity: 0.75;
}

.pg-loading-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 20px 0;
}

.pg-loading-dot {
    width: 12px;
    height: 12px;
    background-color: #086698;
    border-radius: 50%;
    animation: pg-wave 1.4s ease-in-out infinite both;
}

.pg-loading-dot:nth-child(1) {
    animation-delay: -0.32s;
}

.pg-loading-dot:nth-child(2) {
    animation-delay: -0.16s;
}

.pg-loading-dot:nth-child(3) {
    animation-delay: 0s;
}

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
</style>

<script>
jQuery(document).ready(function($) {
    let integrationDataLoaded = false;
    
    // Function to load integration data
    function loadIntegrationData() {
        if (integrationDataLoaded) return;
        
        const $pluginList = $('#pg-integration-plugin-list');
        const $initialLoading = $('#pg-initial-loading');
        
        // Don't add another loading state if initial loading is visible
        // Just keep the existing one
        
        // Make AJAX request to get plugin data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bdtpg_get_plugins',
                nonce: '<?php echo esc_attr( wp_create_nonce('bdtpg_get_plugins_nonce') ); ?>'
            },
            success: function(response) {
                if (response.success && response.data.plugins) {
                    // Hide the initial loading div
                    $initialLoading.hide();
                    renderPluginList(response.data.plugins);
                    integrationDataLoaded = true;
                } else {
                    showError('Unable to load plugin data.');
                }
            },
            error: function() {
                showError('Network error occurred while loading plugin data.');
            }
        });
    }
    
    // Function to render plugin list
    function renderPluginList(plugins) {
        const $pluginList = $('#pg-integration-plugin-list');
        let html = '';
        
        if (plugins.length === 0) {
            html = '<div class="pg-no-plugins" style="text-align: center; padding: 40px;"><p>No plugins found.</p></div>';
        } else {
            plugins.forEach(function(plugin) {
                // Skip own plugin (Pixel Gallery) when printing only; data still includes it for other plugins
                if (plugin.slug === 'pixel-gallery') return;
                const isActive = plugin.status === 'active';
                const isRecommended = plugin.recommended && !isActive;
                
                html += `
                    <label class="pg-plugin-item" data-slug="${plugin.slug}">
                        <span class="bdt-flex bdt-flex-middle bdt-flex-between bdt-margin-small-bottom">
                            <span class="bdt-plugin-logo">
                                ${generatePluginLogo(plugin)}
                            </span>
                            <div class="bdt-plugin-badge-switch-wrap">
                                ${isRecommended ? '<span class="pg-recommended-badge">Recommended</span>' : ''}
                                ${isActive ? '<span class="pg-active-badge">ACTIVE</span>' : ''}
                                ${!isActive ? `
                                    <label class="pg-switch">
                                        <input type="checkbox" class="pg-plugin-slider-checkbox" name="plugins[]" value="${plugin.slug}">
                                        <span class="pg-slider pg-round"></span>
                                    </label>
                                ` : ''}
                            </div>
                        </span>
                        <div class="bdt-flex bdt-flex-middle">
                            <span class="bdt-plugin-name">${plugin.name}</span>
                        </div>
                        <span class="pg-active-installs">
                            Active Installs:
                            <span class="pg-installs-count">${plugin.active_installs_count > 0 ? plugin.active_installs_count.toLocaleString() + '+' : 'Fewer than 10'}</span>
                        </span>
                        ${plugin.downloaded_formatted ? `<span class="pg-downloads">Downloads: ${plugin.downloaded_formatted}</span>` : ''}
                        <div class="pg-rating-section">
                            <div class="pg-wporg-ratings" title="${plugin.rating} out of 5 stars" style="color:var(--wp--preset--color--pomegrade-1, #e26f56);">
                                ${generateStarRating(plugin.rating)}
                            </div>
                            <span class="pg-rating-text">
                                ${plugin.rating} out of 5 stars.
                                ${plugin.num_ratings > 0 ? `<span class="pg-rating-count">(${plugin.num_ratings.toLocaleString()} ratings)</span>` : ''}
                            </span>
                        </div>
                        ${plugin.last_updated_formatted ? `<span class="pg-last-updated">Last Updated: ${plugin.last_updated_formatted}</span>` : ''}
                    </label>
                `;
            });
        }
        
        $pluginList.html(html);
    }
    
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Helper function to generate plugin logo.
    // Logos are bundled with Pixel Gallery, and plugins without one fall back to
    // an initial letter; nothing is loaded from a remote server (WordPress.org
    // disallows offloading assets).
    function generatePluginLogo(plugin) {
        if (plugin.logo) {
            return `<img src="${escapeHtml(plugin.logo)}" alt="" width="48" height="48">`;
        }
        const name = (plugin.name || '').replace(/<[^>]*>/g, '').trim();
        const initial = name ? name.charAt(0).toUpperCase() : '#';
        return `<div class="pg-default-plugin-icon" aria-hidden="true">${escapeHtml(initial)}</div>`;
    }
    
    // Helper function to generate star rating
    function generateStarRating(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = (rating - fullStars) >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let html = '';
        for (let i = 0; i < fullStars; i++) {
            html += '<span class="dashicons dashicons-star-filled"></span>';
        }
        if (hasHalfStar) {
            html += '<span class="dashicons dashicons-star-half"></span>';
        }
        for (let i = 0; i < emptyStars; i++) {
            html += '<span class="dashicons dashicons-star-empty"></span>';
        }
        return html;
    }
    
    // Function to show error
    function showError(message) {
        const $pluginList = $('#pg-integration-plugin-list');
        const $initialLoading = $('#pg-initial-loading');
        
        // Hide initial loading
        $initialLoading.hide();
        
        // Show error in plugin list
        $pluginList.html(`
            <div class="pg-error-state" style="text-align: center; padding: 40px;">
                <p style="color: #d63638;">${message}</p>
                <button type="button" class="bdt-button bdt-button-secondary" onclick="location.reload()">Retry</button>
            </div>
        `);
    }
    
    // Detect when integration tab becomes active
    function observeIntegrationTab() {
        // Check if integration step is currently visible
        const $integrationStep = $('.bdt-setup-wizard-integration');
        
        if ($integrationStep.length) {
            // Create a MutationObserver to detect when the integration step becomes visible
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const $target = $(mutation.target);
                        if ($target.hasClass('bdt-setup-wizard-integration') && $target.is(':visible')) {
                            loadIntegrationData();
                            observer.disconnect(); // Stop observing after first load
                        }
                    }
                });
            });
            
            // Start observing
            observer.observe($integrationStep[0], {
                attributes: true,
                attributeFilter: ['class']
            });
            
            // Also check immediately if it's already visible
            if ($integrationStep.is(':visible') && !integrationDataLoaded) {
                loadIntegrationData();
            }
        }
    }
    
    // Initialize tab observation
    observeIntegrationTab();
    
    // Fallback: Also try to detect tab clicks (for different wizard implementations)
    $(document).on('click', '[data-step="integration"], .bdt-wizard-step[data-step="integration"]', function() {
        if (!integrationDataLoaded) {
            setTimeout(loadIntegrationData, 100);
        }
    });
});
</script>
