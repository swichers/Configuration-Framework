<?php

namespace StevenWichers\Configuration;

use StevenWichers\Configuration\ConfigItem;
use StevenWichers\Platform\PlatformController;
use StevenWichers\Platform\PlatformInterface;
use \Exception;

/**
 * Configuration class for working with site configurations.
 */
abstract class Config {

  // The full path to our primary configuration file.
  protected $config_path = NULL;

  // The parsed contents of the configuration file(s).
  protected $config_contents = array();

  // The platform object for our current platform.
  protected $platform = NULL;

  // The environment and platform specific configuration.
  protected $env_config = array();

  /**
   * Gets the contents of the configuration file.
   *
   * @param string $path
   *   The configuration file path.
   *
   * @return array
   *   The configuration file contents as an array.
   */
  protected abstract function getConfigArray($path);

  /**
   * Get the default configuration path if one is not specified.
   *
   * @return string The path to the configuration file.
   */
  protected abstract function getDefaultConfigPath();

  /**
   * Initializes a Config() object for the given or current platform.
   *
   * @param string $config_path
   *   Force a path to the primary configuration file instead of scanning for
   *   one.
   *
   * @param Platform $platform
   *   A Platform object to override the default platform detection.
   */
  public function __construct($config_path = NULL, PlatformInterface $platform = NULL) {

    $this->config_path = $this->locatePrimaryConfigFile($config_path);
    if (empty($this->config_path)) {

      throw new Exception('Unable to locate configuration file.');
    }

    if (empty($platform)) {

      $platform = (new PlatformController())->getCurrent();
    }

    $this->platform = $platform;
    $this->loadConfig();
  }

  /**
   * Getter for the configuration path.
   *
   * @return string
   *   The full path to the configuration file used.
   */
  public function getConfigPath() {

    return $this->config_path;
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

    $value = empty($this->env_config[$key]) ?
      FALSE :
      $this->env_config[$key];

    return new ConfigItem($key, $value);
  }

  /**
   * Getter for the platform in use.
   *
   * @return Platform
   *   The currently used platform.
   */
  public function getPlatform() {

    // Return a clone so that we do not allow outside code to interact with our
    // platform object.
    return clone $this->platform;
  }

  /**
   * Recursively locates the configuration file with the given name.
   *
   * Will scan from the current directory up until it finds the file at the path
   * specified.
   *
   * @param string $path
   *   The path (and name) of the configuration file to search for.
   *
   * @param string $root
   *   The directory to start scanning from. Defaults to the current directory.
   *
   * @param boolean $recurse
   *   TRUE to allow recursively scanning up the directory tree for the
   *   configuration file. Searching for
   *   /var/www/example.com/htdocs/example/config.json will also scan:
   *     /var/www/example.com/htdocs/config.json
   *     /var/www/example.com/config.json
   *     /var/www/config.json
   *     /var/config.json
   *     /config.json
   *
   * @return string|bool
   *   The path to the configuration file or FALSE.
   */
  protected function locatePrimaryConfigFile($path = NULL, $root = NULL, $recurse = TRUE) {

    if (is_null($path)) {

      $path = $this->getDefaultConfigPath();
    }

    if (empty($path)) {

      return FALSE;
    }

    // Default to current directory if we weren't told to look elsewhere.
    if (empty($root)) {

      $root = getcwd();
    }

    $config_path = sprintf('/%s/%s', trim($root, '/'), ltrim($path, '/'));

    if (is_readable($config_path)) {

      return $config_path;
    }
    elseif (empty($recurse)) {

      return FALSE;
    }

    $root_parts = explode(DIRECTORY_SEPARATOR, $root);
    // Explode will return an empty string when we recurse too far, so filter it out.
    $root_parts = array_filter($root_parts);

    array_pop($root_parts);
    if (empty($root_parts)) {

      return FALSE;
    }

    return $this->locatePrimaryConfigFile($path, implode(DIRECTORY_SEPARATOR, $root_parts), $recurse);
  }

  /**
   * Detect and load configuration for the current platform and environment.
   */
  protected function loadConfig() {

    $path = pathinfo($this->getConfigPath(), PATHINFO_DIRNAME);
    $filename = pathinfo($this->getConfigPath(), PATHINFO_BASENAME);
    $platform_name = $this->platform->getNormalizedName();
    $environment = $this->platform->getEnvironment();

    // We need to support loading of multiple different configuration files that
    // can overwrite configuration on a platform and environment level.
    foreach ($this->getTokenedFilenames($filename, TRUE) as $filename) {

      $config_path = $path . '/' . $filename;
      if (!is_readable($config_path)) {

        continue;
      }

      $this->config_contents[$filename] = $this->getConfigArray($config_path);
    }

    // Build up the finalized configuration with platform and env overrides.
    $this->env_config = $this->getFlattenedConfig();
  }

  /**
   * Insert the given tokens into the filename for dynamic platform/env filenames.
   *
   * Used for turning config.json into config.dev.json.
   *
   * @param array $new_parts
   *   The new parts to add to the filename.
   *
   * @return string
   *   The modified filename.
   */
  public function getTokenedFilename($filename, array $new_parts) {

    $parts = pathinfo($filename);
    $filename_parts = array($parts['filename'], $parts['extension']);
    array_splice($filename_parts, 1, 0, $new_parts);

    return implode('.', $filename_parts);
  }

  /**
   * Returns a flattened configuration array based on environment and platform.
   *
   * This will take in all configuration arrays loaded and merge them into a
   * single configuration array with no env/platform subsections.
   *
   * @return array
   *   The flattened configuration array.
   */
  protected function getFlattenedConfig() {

    $platform_name = $this->platform->getNormalizedName();
    $environment = $this->platform->getEnvironment();

    $flattened = array();

    foreach ($this->config_contents as $filename => $config) {

      // Replace any defaults defined in successive configurations.
      if (!empty($config['default'])) {

        $flattened = array_replace_recursive($flattened, $config['default']);
      }

      // Replace any environment specific default overrides.
      if (!empty($config[$environment])) {

        $flattened = array_replace_recursive($flattened, $config[$environment]);
      }

      // Load in any platform specific default overrides that are defined.
      if (!empty($config[$platform_name]['default'])) {

        $flattened = array_replace_recursive($flattened, $config[$platform_name]['default']);
      }

      // Load platform and environment specific overrides.
      if (!empty($config[$platform_name][$environment])) {

        $flattened = array_replace_recursive($flattened, $config[$platform_name][$environment]);
      }
    }

    return $flattened;
  }

  /**
   * Gets env/platform versions of the given filename.
   *
   * @param string $filename
   *   The filename to get tokened versions of.
   *
   * @param bool $include_original
   *   TRUE to include the original filename in the returned list.
   *
   * @param bool $include_default
   *   TRUE to include a default version of the filename in the returned list.
   *
   * @return array
   *   An array of the given filename with the current platform and environment
   *   names inserted.
   */
  public function getTokenedFilenames($filename, $include_original = FALSE, $include_default = TRUE) {

    $platform_name = $this->platform->getNormalizedName();
    $environment = $this->platform->getEnvironment();

    // These different names are ordered from most generic to most specific.
    $filenames = array(
      $this->getTokenedFilename($filename, array($platform_name)),
      $this->getTokenedFilename($filename, array($environment)),
      $this->getTokenedFilename($filename, array($platform_name, $environment)),
    );

    if ($include_default) {

      array_unshift($filenames, $this->getTokenedFilename($filename, array('default')));
    }

    if ($include_original) {

      array_unshift($filenames, $filename);
    }

    return $filenames;
  }
}
