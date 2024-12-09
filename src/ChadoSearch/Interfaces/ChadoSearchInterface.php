<?php

namespace Drupal\chado_search\ChadoSearch\Interfaces;

/**
 * Interface for chado_search plugins.
 */
interface ChadoSearchInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

}
