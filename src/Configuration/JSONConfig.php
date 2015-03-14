<?php

namespace StevenWichers\Configuration;

/**
 * Configuration class for working with JSON site configurations.
 */
class JSONConfig extends Config {

  /**
   * Gets the contents of the configuration file.
   *
   * @param string $path
   *   The configuration file path.
   *
   * @return array
   *   The configuration file contents as an array.
   */
  protected function getConfigArray($path) {

    $contents = file_get_contents($path);
    $config_contents = json_decode($contents, TRUE);

    return empty($config_contents) ? array() : $config_contents;
  }

  /**
   * Get the default configuration path if one is not specified.
   *
   * @return string The path to the configuration file.
   */
  protected function getDefaultConfigPath() {

    // The default location of our configuration file.
    return 'config.json';
  }
}
