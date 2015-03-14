<?php

namespace StevenWichers\Configuration;

/**
 * Extends the normal ConfigItem to allow for similar "array" access to .env
 * files.
 */
class DotEnvConfigItem extends ConfigItem {

  protected $chain = array();

  /**
   * Initialize the configuration object.
   *
   * @param string $key
   *   The name of this configuration item.
   *
   * @param array $parents
   *   The parent keys used when chaining config items. This information is
   *   used to support chaining environment variable names.
   */
  public function __construct($key, array $parents = array()) {

    $this->key = $key;

    $this->chain = empty($parents) ? array() : $parents;
    $this->chain[] = $key;

    $value = getenv(strtoupper(implode('_', $this->chain)));
    $this->value = $value ? $value : NULL;
  }

  /**
   * Gets the name of the configuration item.
   *
   * @param bool $env_key
   *   TRUE to return the key as checked against the environment variables.
   *   FALSE to return the key as requested.
   *
   * @return string
   *   The name of the configuration item.
   */
  public function getKey($env_key = FALSE) {

    return $env_key ? $this->getEnvironmentKey() : $this->key;
  }

  public function __get($key) {

    return new self($key, $this->chain);
  }

  /**
   * Gets the variable key to use when reading from the environment array.
   *
   * @return string
   *   The variable key to use with getenv().
   */
  protected function getEnvironmentKey() {

    $key = implode('_', $this->chain);

    // getenv is supposed to be case insensitive, but this actually only applies
    // on Windows. Uppercase environment variables are the norm, so force all
    // getenv calls to use uppercase keys.
    $key = strtoupper($key);

    return $key;
  }
}
