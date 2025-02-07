<?php

namespace Drupal\Tests\trpcultivate_chadosearch\Fixtures;

use Drupal\Core\Database\Query\Select;
use Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\trpcultivate_chadosearch\ChadoSearch\ChadoSearchPluginBase;

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
class ChadoSearchBasicallyBase extends ChadoSearchPluginBase implements ChadoSearchInterface {

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
  public static array $info = [
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
      'column2' => [
        'title' => 'Column2',
        'help' => 'The second filter.',
      ],
      'column3' => [
        'title' => 'Column3',
        'help' => 'The third filter.',
        'default' => 'FRED',
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function getQuery(Select|null &$query, int $offset = 0) {

    if ($offset === 5) {
      return NULL;
    }

    if ($offset === 10) {
      $query = $this->chado_connection->select('1:organism', 'o')
        ->fields('o', ['genus', 'species']);
      return $query;
    }
  }

}
