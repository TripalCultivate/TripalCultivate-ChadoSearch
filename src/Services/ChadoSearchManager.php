<?php

namespace Drupal\chado_search\Services;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\chado_search\ChadoSearch\Annotation\ChadoSearch;
use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;

/**
 * Manages ChadoSearch Plugin instances.
 *
 * Creates search interfaces based on the information provided by specific
 * plugin instances including managing the form and route.
 */
final class ChadoSearchManager extends DefaultPluginManager {

  /**
   * Constructs the ChadoSearchManager object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ChadoSearch',
      $namespaces,
      $module_handler,
      ChadoSearchInterface::class,
      ChadoSearch::class,
    );
    $this->alterInfo('chado_search_info');
    $this->setCacheBackend($cache_backend, 'chado_search_plugins');
  }

}
