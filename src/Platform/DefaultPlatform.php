<?php

namespace StevenWichers\Platform;

use \Exception;

/**
 * A default platform to fallback to.
 */
class DefaultPlatform extends Platform {

  /**
   * Initialize the platform object.
   */
  public function __construct() {

    $this->environment = 'local';
  }

  /**
   * Get the name of this platform.
   *
   * @return string
   *   The name of the platform.
   */
  public function getName() {

    return 'Default Platform';
  }
}
