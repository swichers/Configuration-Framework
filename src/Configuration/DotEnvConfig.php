<?php

namespace StevenWichers\Configuration;

/**
 * Configuration class for working with .env files.
 *
 * @see https://github.com/vlucas/phpdotenv
 */
class DotEnvConfig extends Config {

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

    // Dotenv doesn't provide a way to know what items were loaded and this
    // method is required to be implemented.
    return array();
  }

  /**
   * Get the default configuration path if one is not specified.
   *
   * @return string The path to the configuration file.
   */
  protected function getDefaultConfigPath() {

    // The default location of our configuration file.
    return '.env';
  }

  /**
   * Gets a configuration item for the given key.
   *
   * @param string $key
   *   The name of the item to get the configuration for.
   *
   * @return ConfigItem
   *   A ConfigItem object (even for non-existent keys).
   */
  public function getConfigItem($key) {

    return new DotEnvConfigItem($key);
  }

  /**
   * Detect and load configuration for the current platform and environment.
   */
  protected function loadConfig() {

    $path = pathinfo($this->getConfigPath(), PATHINFO_DIRNAME);
    $filename = pathinfo($this->getConfigPath(), PATHINFO_BASENAME);

    \Dotenv::load($path, $filename);
  }
}
