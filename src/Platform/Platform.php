<?php

namespace StevenWichers\Platform;

/**
 * Abstract for working with platforms and environments.
 */
abstract class Platform implements PlatformInterface {

  // The specific environment we're on (dev, tst, stg).
  protected $environment = NULL;

  /**
   * Check if the current platform is in use.
   *
   * @return boolean
   *   TRUE if this is the current platform, FALSE otherwise.
   */
  public function isCurrent() {

    return !empty($this->environment);
  }

  /**
   * Gets a machine name for the platform.
   *
   * @return string
   *   The platform's name in a machine safe format.
   */
  public function getNormalizedName() {

    $name = str_replace(' ', '_', $this->getName());
    $name = strtolower($name);

    return preg_replace('/[^a-z0-9_]/i', '', $name);
  }

  /**
   * Gets the current environment on the platform (dev, tst, etc.).
   *
   * @return string
   *   The current environment.
   */
  public function getEnvironment() {

    return trim($this->environment);
  }

  /**
   * Get the current platform's name.
   *
   * @return string
   *   The platform's name.
   */
  abstract public function getName();
}
