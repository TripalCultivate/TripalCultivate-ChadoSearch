<?php

namespace Drupal\chado_search\Services;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\chado_search\ChadoSearch\Annotation\ChadoSearch;
use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Symfony\Component\Routing\Route;

/**
 * Manages ChadoSearch Plugin instances.
 *
 * Creates search interfaces based on the information provided by specific
 * plugin instances including managing the form and route.
 */
class ChadoSearchManager extends DefaultPluginManager {

  /**
   * Constructs the ChadoSearchManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   An instance of the cache backend which is used to cache plugin
   *   definitions for performance reasons.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service which is used to invoke the alter hook.
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

  /**
   * Registers routes for all the ChadoSearch plugin instances.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];

    // Get a list of all the ChadoSearch plugin instances.
    $search_instances = $this->getDefinitions();

    // For each instance... create a route object and add it to the list.
    foreach ($search_instances as $search_definition) {
      $route_name = 'chado_search.form.' . $search_definition['id'];
      $routes[$route_name] = new Route(
        $search_definition['url_path'],
        [
          '_form' => '\Drupal\chado_search\Form\ChadoSearchForm',
          'instance_id' => $search_definition['id'],
          '_title' => (string) $search_definition['title'],
        ],
        [
          '_permission' => implode('+', $search_definition['permissions']),
        ]
      );
    }

    return $routes;
  }

}
