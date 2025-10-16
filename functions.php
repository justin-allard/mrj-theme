<?php
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
  'https://github.com/justin-allard/mrj-theme/',
  __FILE__,
  'mrjtheme'
);

$myUpdateChecker->setBranch('main');