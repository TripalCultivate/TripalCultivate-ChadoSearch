<?php

namespace Drupal\chado_search\ChadoSearch\Interfaces;

/**
 * Interface for chado_search plugins.
 */
interface ChadoSearchInterface {

  /**
   * Defines the query and arguments to use for the search.
   *
   * @param string $query
   *   The full SQL query to execute. This will be executed using chado_query()
   *   so use curly brackets appropriately. Use :placeholders for any values.
   * @param array $args
   *   An array of arguments to pass to chado_query(). Keys must be the
   *   placeholders in the query and values should be what you want them set to.
   * @param int $offset
   *   The current offset. This is primarily used for pagers.
   */
  public function getQuery(string &$query, array &$args, int $offset);

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

}
