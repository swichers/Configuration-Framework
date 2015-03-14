<?php

namespace StevenWichers\Configuration;

/**
 * Wrapper for individual configuration items.
 *
 * Makes chaining configuration on non-existent keys possible.
 */
class ConfigItem {

  // The name of this configuration item.
  protected $key = NULL;
  // The value of this configuration item.
  protected $value = NULL;

  /**
   * Initialize the configuration object.
   *
   * @param string $key
   *   The name of this configuration item.
   *
   * @param mixed $value
   *   The value of this configuration item.
   */
  public function __construct($key, $value) {

    $this->key = $key;
    $this->value = $value;
  }

  /**
   * Getter for the configuration item.
   *
   * @param mixed $default
   *   A default value to return if the current value is NULL.
   *
   * @return mixed
   *   The value of the configuration item.
   */
  public function getValue($default = NULL) {

    if (is_null($this->value)) {

      return $default;
    }

    return $this->value;
  }

  /**
   * Gets the name of the configuration item.
   *
   * @return string
   *   The name of the configuration item.
   */
  public function getKey() {

    return $this->key;
  }

  public function __get($key) {

    $value = isset($this->value[$key]) ?
      $this->value[$key] :
      NULL;

    return new self($key, $value);
  }

  public function __toString() {

    if (is_array($this->value)) {

      return serialize($this->value);
    }

    return $this->value;
  }
}
