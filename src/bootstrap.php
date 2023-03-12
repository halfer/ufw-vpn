<?php

// Manually load classes for now
// @todo Add autoloader in the future
$root = realpath(__DIR__ . '/..');

require_once $root . '/vendor/autoload.php';
require_once $root . '/src/UfwVpn/Diff.php';
require_once $root . '/src/UfwVpn/Firewall.php';
require_once $root . '/src/UfwVpn/RuleDiff.php';
