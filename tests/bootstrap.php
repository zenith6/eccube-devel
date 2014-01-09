<?php

require_once 'PHPUnit/Autoload.php';
require_once 'SplClassLoader.php';

$lib_dir = __DIR__ . '/../src/library';
$loader = new SplClassLoader(null, $lib_dir);
$loader->register();
