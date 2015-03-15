<?php

namespace StevenWichers\Platform;

use \Exception;

/**
 * A controller class to make working with platforms easier.
 */
class PlatformController {

  // A list of valid environments.
  protected $valid_environments = ['local', 'dev', 'tst', 'stg', 'prd'];
  // A list of registered platforms.
  protected $checkers = [];

  /**
   * Registers a platform for use.
   *
   * @param string $checker
   *   The name of the platform to register. Must match the first part of the
   *   class name.
   */
  protected function registerChecker($checker, $namespace = __NAMESPACE__) {

    // Class name must be fully namespaced.
    $class_name = sprintf('\%s\%sPlatform', $namespace, $checker);
    if (!class_exists($class_name)) {

      throw new Exception(sprintf('Platform checker %s does not exist!', $class_name));
    }

    $this->checkers[] = $class_name;
  }

  /**
   * Initialize the platform controller.
   */
  public function __construct() {

    // Register the available platforms.
    $this->registerChecker('Acquia');

    // Default should be last.
    $this->registerChecker('Default');
  }

  /**
   * Checks if the given or current platform is valid.
   *
   * @param Platform $platform
   *   The platform to check. If not specified the current platform will be
   *   used.
   *
   * @return boolean
   *   TRUE if the platform is valid, FALSE otherwise.
   *
   * @todo Should this be moved into Platform and each platform implement it?
   */
  public function isValid(Platform $platform = NULL) {

    $platform = $platform ?: $this->getCurrent();

    return in_array($platform->getEnvironment(), $this->valid_environments);
  }

  /**
   * Gets the current platform.
   *
   * @return bool|Platform
   *   A Platform object or FALSE.
   */
  public function getCurrent() {

    foreach ($this->checkers as $checker) {

      $obj = new $checker();
      if ($obj->isCurrent()) {

        return $obj;
      }
    }

    return FALSE;
  }

  /**
   * Get a list of valid environments.
   *
   * @return array
   *   An array of valid environments.
   */
  public function getValidEnvironments() {

    return $this->valid_environments;
  }
}
