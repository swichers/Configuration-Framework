<?php

namespace StevenWichers\Platform;

use \Exception;

/**
 * Determines if we are on the Acquia platform.
 */
class AcquiaPlatform extends Platform {

  // The file containing the function ah_site_info() on Acquia's servers.
  const SITE_SCRIPT_FILE = '/var/www/site-scripts/site-info.php';

  // Site information as returned by ah_site_info().
  protected $site_info = [];

  /**
   * Determine if we are using Acquia Cloud servers.
   *
   * @return boolean
   *   TRUE if we are on Acquia Cloud servers.
   */
  protected function isAcquiaServer() {

    return is_readable(self::SITE_SCRIPT_FILE);
  }

  /**
   * Determine if we are using Acquia dev desktop.
   *
   * @return boolean
   *   TRUE if we are using Acquia dev desktop.
   */
  protected function isAcquiaDesktop() {

    return !empty($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR']);
  }

  /**
   * Get Acquia server settings.
   *
   * Will populate _ENV vars if necessary.
   */
  protected function getAcquiaServerSettings() {

    if (!$this->isAcquiaServer()) {

      return FALSE;
    }

    require_once self::SITE_SCRIPT_FILE;

    if (!function_exists('ah_site_info')) {

      throw new Exception('Acquia site script did not contained expected functions.');
    }

    list($this->site_info['name'],
         $this->site_info['group'],
         $this->site_info['stage'],
         $this->site_info['secret']) = ah_site_info();

    // Duplicate some of the functionality of ah_settings_include() because we
    // can't actually include the settings file due to its use of require vs
    // require_once.
    if (empty($_ENV['AH_SITE_NAME'])) {

      $_ENV['AH_SITE_NAME'] = $this->site_info['name'];
    }

    if (empty($_ENV['AH_SITE_ENVIRONMENT'])) {

      $_ENV['AH_SITE_ENVIRONMENT'] = $this->site_info['stage'];
    }

    if (empty($_ENV['AH_SITE_GROUP'])) {

      $_ENV['AH_SITE_GROUP'] = $this->site_info['group'];
    }
  }

  /**
   * Get the environment from system variables.
   *
   * @return string
   *   The current environment.
   */
  protected function getEnvFromVars() {

    $env = NULL;

    if (!empty($_SERVER['AH_SITE_ENVIRONMENT'])) {

      $env = $_SERVER['AH_SITE_ENVIRONMENT'];
    }
    elseif (!empty($_ENV['AH_SITE_ENVIRONMENT'])) {

      $env = $_ENV['AH_SITE_ENVIRONMENT'];
    }

    return $env;
  }

  /**
   * Initialize the platform object.
   */
  public function __construct() {

    if ($this->isAcquiaServer()) {

      $this->getAcquiaServerSettings();
      $this->environment = $this->getEnvFromVars();
    }
    elseif ($this->isAcquiaDesktop()) {

      // There is no good way to determine working env on Acquia desktop at this
      // time. Assume dev as that is the most likely case.
      $this->environment = 'dev';
    }
  }

  /**
   * Get the name of this platform.
   *
   * @return string
   *   The name of the platform.
   */
  public function getName() {

    return 'Acquia';
  }
}
