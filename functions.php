<?php
require get_template_directory() . '/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/justin-allard/mrj-theme/',
    get_template_directory() . '/functions.php',
    'mrj-theme'
);

$myUpdateChecker->setBranch('main');

$api = $myUpdateChecker->getVcsApi();
if ($api) {
    $api->enableReleaseAssets();
}

//add_action('admin_notices', function() use ($myUpdateChecker) {
//    $state = $myUpdateChecker->getUpdateState();
//    echo '<pre>'; print_r($state); echo '</pre>';
//});




//Add style.css to site
function mrj_theme_enqueue_styles() {
    wp_enqueue_style( 'mrj-theme-style', get_stylesheet_uri() );
}



// Schedule daily ping on activation
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('mrjtheme_daily_ping')) {
        wp_schedule_event(time(), 'daily', 'mrjtheme_daily_ping');
    }
});

// Clear scheduled event on deactivation
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('mrjtheme_daily_ping');
});

// Daily ping callback
add_action('mrjtheme_daily_ping', function () {
    // Get the site URL
    $site_url = get_site_url();

    // Normalize: lowercase, strip protocol, remove trailing slash, strip www.
    $site_url = strtolower(trim($site_url));
    $site_url = preg_replace('#^https?://#', '', $site_url);
    $site_url = rtrim($site_url, '/');
    $site_url = preg_replace('#^www\.#', '', $site_url);

    // Send POST request to API
    wp_remote_post(
        'https://api.mrjtheme.com/api.php',
        [
            'timeout'  => 5,
            'blocking' => false,
            'headers'  => [
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode([
                'siteURL'       => $site_url,
                'ThemeVersion' => wp_get_theme()->get('Version'),
                'SiteVersion'  => get_bloginfo('version'),
                'SiteTitle'    => get_bloginfo('name')
            ])
        ]
    );
});
