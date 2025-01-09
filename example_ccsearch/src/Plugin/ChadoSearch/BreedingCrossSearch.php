<?php

namespace Drupal\example_ccsearch\Plugin\ChadoSearch;

use Drupal\chado_search\ChadoSearch\ChadoSearchPluginBase;

/**
 * Creates an example search for breeding crosses using the chado search api.
 *
 *  @ChadoSearch(
 *    id = "example_breeding_cross",
 *    title = @Translation("EXAMPLE: Breeding Crosses"),
 *    description = @Translation("Provides an example search for breeding crosses using the chado search api."),
 *    permissions = {"access content"},
 *    url_path = "search-crosses",
 *    button_text = @Translation("Search"),
 *    require_submit = FALSE,
 *    pager = TRUE,
 *    num_items_per_page = 25,
 *  )
 */
class BreedingCrossSearch extends ChadoSearchPluginBase {

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static $info = [
    // Lists the columns in your results table.
    'fields' => [
      'child_name' => [
        'title' => 'Cross Name',
      ],
      'mom_name' => [
        'title' => 'Maternal Parent',
      ],
      'dad_name' => [
        'title' => 'Paternal Parent',
      ],
    ],
    // The filter criteria available to the user.
    // This is used to generate a search form which can be altered.
    'filters' => [
      'genus' => [
        'title' => 'Genus',
        'help' => 'The genus the germplasm belongs to (e.g. Lens).',
      ],
      'species' => [
        'title' => 'Species',
        'help' => 'The species the germplasm belongs to (e.g. culinaris).',
      ],
      'child_name' => [
        'title' => 'Cross Number',
        'help' => 'The unique cross number within the breeding program.',
      ],
      'mom_name' => [
        'title' => 'Maternal Parent',
        'help' => 'The germplasm used as the maternal parent for the cross.',
      ],
      'dad_name' => [
        'title' => 'Paternal Parent',
        'help' => 'The germplasm used as the paternal parent for the cross.',
      ],
    ],
  ];

  /**
   * Determine the query for the breeding cross search.
   *
   * Searches for the parents of a given cross. It can be filtered by cross
   * name or either parent.
   *
   * ASSUMPTION: Parents are connected to a cross by is_maternal_parent and
   *   is_paternal_parent cvterms.
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

    // Retrieve the filter results already set.
    $filter_results = $this->values;

    $query = "SELECT
        child.name as child_name, mom.name as mom_name, dad.name as dad_name
       FROM {1:stock} mom
       LEFT JOIN {1:stock_relationship} relmom ON relmom.subject_id=mom.stock_id
       LEFT JOIN {1:stock} child ON relmom.object_id=child.stock_id
       LEFT JOIN {1:stock_relationship} reldad ON reldad.object_id=child.stock_id
       LEFT JOIN {1:stock} dad ON dad.stock_id=reldad.subject_id
       WHERE
         relmom.type_id IN (SELECT cvterm_id FROM {1:cvterm} WHERE name~'maternal') AND
         reldad.type_id IN (SELECT cvterm_id FROM {1:cvterm} WHERE name~'paternal')";

    // Now we add the where arguments based on the filter results.
    // NOTE: make your placeholders match the key in the $filter_results array.
    // - Cross Name.
    if (!empty($filter_results['cross_name'])) {
      $query .= ' AND child.name = :cross_name';
      $args[':cross_name'] = $filter_results['cross_name'];
    }

    // - Maternal Parent.
    if (!empty($filter_results['mom_name'])) {
      $query .= ' AND mom.name = :mom_name';
      $args[':mom_name'] = $filter_results['mom_name'];
    }

    // - Paternal Parent.
    if (!empty($filter_results['dad_name'])) {
      $query .= ' AND dad.name = :dad_name';
      $args[':dad_name'] = $filter_results['dad_name'];
    }

    $query .= ' ORDER BY child.name ASC';

    // Add the offset to the query for paging.
    if ($offset and is_numeric($offset)) {
      $query .= 'OFFSET ' . $offset;
    }
    $query .= ' LIMIT 50';
  }

}
