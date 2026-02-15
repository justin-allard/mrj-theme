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


define( 'MRJ_PING_HOOK', 'mrjtheme_daily_ping' );

add_action( 'after_switch_theme', function () {
    if ( ! wp_next_scheduled( MRJ_PING_HOOK ) ) {
        wp_schedule_event( time(), 'daily', MRJ_PING_HOOK );
    }
});

add_action( 'switch_theme', function () {
    wp_clear_scheduled_hook( MRJ_PING_HOOK );
});

add_action( MRJ_PING_HOOK, function () {

    $site_url = home_url();
    $site_url = strtolower(trim($site_url));
    $site_url = preg_replace('#^https?://#', '', $site_url);
    $site_url = rtrim($site_url, '/');
    $site_url = preg_replace('#^www\.#', '', $site_url);

    wp_remote_post(
        'https://api.mrjtheme.com/api.php',
        [
            'timeout'  => 10,
            'blocking' => false,
            'headers'  => [
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode([
                'siteURL'      => $site_url,
                'ThemeVersion' => wp_get_theme()->get('Version'),
                'SiteVersion'  => get_bloginfo('version'),
                'SiteTitle'    => get_bloginfo('name')
            ])
        ]
    );
});
