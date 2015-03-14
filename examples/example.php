<?php

// Need to change the current directory because the script can be run from
// anywhere and mess up the config autoloading.
chdir(__DIR__);

require '../vendor/autoload.php';

use StevenWichers\Configuration\JSONConfig;
use StevenWichers\Configuration\DotEnvConfig;

/*
  Examples of pulling information out of the configuration:
 */
if ('cli' !== php_sapi_name()) { echo '<pre><xmp>'; }

// $platform = new ExamplePlatform();

$config_classes = array(
  'JSONConfig' => 'config/config.json',
  'DotEnvConfig' => '.env',
);
foreach ($config_classes as $class_name => $config_path) {

  printf("Creating new configuration object of type: %s\n\n", $class_name);

  // Yay PHP... In order to have dynamic class names we still have to specify
  // the namespace even though we declare the namespace above.
  $class_name = 'StevenWichers\\Configuration\\' . $class_name;

  $config = new $class_name($config_path);

  echo 'Get a root level value: ';
  echo $config->getConfigItem('fqdn')->getValue(), PHP_EOL, PHP_EOL;
  echo 'Get a complex (array) value:', PHP_EOL, PHP_EOL;
  print_r($config->getConfigItem('db')->getValue()); echo PHP_EOL, PHP_EOL;
  echo 'Get a value from a complex value: ';
  echo $config->getConfigItem('db')->user->getValue(), PHP_EOL, PHP_EOL;
  echo 'Try and get a non-existant value with no default: ';
  echo $config->getConfigItem('db')->user->nothing->getValue(), PHP_EOL, PHP_EOL;
  echo 'Try and get a non-existant value with a default: ';
  echo $config->getConfigItem('db')->user->nothing->getValue('something'), PHP_EOL, PHP_EOL;
  echo 'Get the key for a non-existant value: ';
  echo $config->getConfigItem('db')->user->nothing->getKey(), PHP_EOL, PHP_EOL;

  echo 'Get the name of the platform we are on: ';
  echo $config->getPlatform()->getName(), PHP_EOL, PHP_EOL;
  echo 'Get the name of the environment we are on (dev, local, etc): ';
  echo $config->getPlatform()->getEnvironment(), PHP_EOL, PHP_EOL;
  echo 'Get a platform specific config override: ';
  echo $config->getConfigItem('name')->getValue(), PHP_EOL, PHP_EOL;
  echo 'Get a platform and environment specific config override: ';
  echo $config->getConfigItem('cron')->getValue(), PHP_EOL, PHP_EOL;

  echo 'Get possible settings.php files: ';
  print_r($config->getTokenedFilenames('settings.php')); echo PHP_EOL, PHP_EOL;
  echo str_repeat('-', 80), PHP_EOL, PHP_EOL;
}

if ('cli' !== php_sapi_name()) { echo '</xmp></pre>'; }
