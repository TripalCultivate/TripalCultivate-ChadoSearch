<?php

namespace Drupal\Tests\chado_search\Fixtures;

use Drupal\chado_search\ChadoSearch\ChadoSearchPluginBase;

/**
 * Creates a fake plugin class to test that is essentially the base class.
 *
 *  @ChadoSearch(
 *    id = "less_than_1page",
 *    title = @Translation("Search Less than one page"),
 *    description = @Translation("A Fake plugin instance to test the base plugin class."),
 *    permissions = {"access content"},
 *    url_path = "search-fakers",
 *    button_text = @Translation("Search"),
 *    require_submit = FALSE,
 *    pager = TRUE,
 *    num_items_per_page = 6,
 *  )
 */
class ChadoSearchLess1Page extends ChadoSearchPluginBase {

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
      'column2' => [
        'title' => 'Column2',
        'help' => 'The second filter.',
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function getQuery(string &$query, array &$args, int $offset) {

  }

  /**
   * {@inheritdoc}
   */
  public function getResults($offset): array|FALSE {

    $results = [];

    $results[] = [
      'column1' => 'c1-r1',
      'column2' => 'c2-r1',
    ];

    $results[] = [
      'column1' => 'c1-r2',
      'column2' => 'c2-r2',
    ];

    $results[] = [
      'column1' => 'c1-r3',
      'column2' => 'c2-r3',
    ];

    if ($offset == 0) {
      return $results;
    }
    else {
      return [];
    }
  }

}
