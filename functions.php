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


<?php
define( 'MRJ_PING_HOOK', 'mrjtheme_daily_ping' );

/**
 * Add custom cron schedule (every 12 hours)
 */
add_filter( 'cron_schedules', function ( $schedules ) {
    $schedules['every_12_hours'] = [
        'interval' => 12 * HOUR_IN_SECONDS,
        'display'  => 'Every 12 Hours'
    ];
    return $schedules;
});

/**
 * Schedule event on theme activation
 */
add_action( 'after_switch_theme', function () {

    if ( ! wp_next_scheduled( MRJ_PING_HOOK ) ) {

        // Get site timezone timestamp
        $timezone = wp_timezone();
        $now = new DateTime( 'now', $timezone );

        // Determine next 6am or 6pm
        $hour = (int) $now->format('G');

        if ( $hour < 6 ) {
            $now->setTime( 6, 0 );
        } elseif ( $hour < 18 ) {
            $now->setTime( 18, 0 );
        } else {
            $now->modify( '+1 day' )->setTime( 6, 0 );
        }

        wp_schedule_event( $now->getTimestamp(), 'every_12_hours', MRJ_PING_HOOK );
    }
});

/**
 * Clear event when theme is switched
 */
add_action( 'switch_theme', function () {
    wp_clear_scheduled_hook( MRJ_PING_HOOK );
});

/**
 * Daily ping callback
 */
add_action( MRJ_PING_HOOK, function () {

    $site_url = home_url();
    $site_url = strtolower( trim( $site_url ) );
    $site_url = preg_replace( '#^https?://#', '', $site_url );
    $site_url = rtrim( $site_url, '/' );
    $site_url = preg_replace( '#^www\.#', '', $site_url );

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
