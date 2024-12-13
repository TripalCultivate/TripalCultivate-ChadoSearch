<?php

namespace Drupal\Tests\chado_search\Fixtures;

use Drupal\chado_search\ChadoSearch\ChadoSearchPluginBase;

/**
 * Creates a fake plugin class to test that is essentially the base class.
 *
 *  @ChadoSearch(
 *    id = "basically_base",
 *    title = @Translation("Basically Base"),
 *    description = @Translation("A Fake plugin instance to test the base plugin class."),
 *    permissions = {"access content"},
 *    url_path = "search-fakers",
 *    button_text = @Translation("Search"),
 *    require_submit = TRUE,
 *    pager = TRUE,
 *    num_items_per_page = 25,
 *  )
 */
class ChadoSearchBasicallyBase extends ChadoSearchPluginBase {

  /**
   * Add CSS/JS to the form/results page through libraries.
   *
   * NOTE: the libraries listed here must already be defined in the
   * libraries.yml file according to Drupal standards.
   *
   * @var array
   */
  public static array $attached = [
    'library1',
    'library2',
    'library3',
  ];

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static $info = [
    // Lists the columns in your results table.
    'fields' => [
      'column1' => [
        'title' => 'Column1',
      ],
      'column2' => [
        'title' => 'Column2',
      ],
    ],
    // The filter criteria available to the user.
    // This is used to generate a search form which can be altered.
    'filters' => [
      'column1' => [
        'title' => 'Column1',
        'help' => 'The first filter.',
      ],
      'mom_name' => [
        'title' => 'Column2',
        'help' => 'The second filter.',
      ],
    ],
  ];

  /**
   * The query method.
   *
   * @param string $query
   *   The full SQL query to execute. This will be executed using chado_query()
   *   so use curly brackets appropriately. Use :placeholders for any values.
   * @param array $args
   *   An array of arguments to pass to chado_query(). Keys must be the
   *   placeholders in the query and values should be what you want them set to.
   * @param int $offset
   *   The number of records to offset for the results. This is used in paging.
   */
  public function getQuery(string &$query, array &$args, int $offset) {

  }

}
