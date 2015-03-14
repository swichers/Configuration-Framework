<?php

namespace StevenWichers\Platform;

/**
 * Platform interface.
 */
interface PlatformInterface {

  public function isCurrent();
  public function getNormalizedName();
  public function getEnvironment();
  public function getName();
}
