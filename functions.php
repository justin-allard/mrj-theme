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
