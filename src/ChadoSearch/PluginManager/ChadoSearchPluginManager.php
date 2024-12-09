<?php

namespace Drupal\chado_search\ChadoSearch\PluginManager;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\chado_search\ChadoSearch\Annotation\ChadoSearch;
use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;

/**
 * ChadoSearch plugin manager.
 */
final class ChadoSearchPluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
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
