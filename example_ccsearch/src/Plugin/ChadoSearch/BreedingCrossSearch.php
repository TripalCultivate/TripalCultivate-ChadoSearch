<?php

namespace Drupal\example_ccsearch\Plugin\ChadoSearch;

use Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\trpcultivate_chadosearch\ChadoSearch\ChadoSearchPluginBase;
use Drupal\Core\Database\Query\Select;

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
class BreedingCrossSearch extends ChadoSearchPluginBase implements ChadoSearchInterface {

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static $info = [
    // Lists the columns in your results table.
    'fields' => [
      'cross_name' => [
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
      'cross_name' => [
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
   * @param Drupal\Core\Database\Query\Select|null $query
   *   A Drupal select query object used to retrieve the results.
   *   This parameter will be NULL when requested and initialized inside
   *   this method.
   * @param int $offset
   *   The number of records to offset for the results. This is used in paging.
   */
  public function getQuery(Select|null &$query, int $offset = 0) {

    // Retrieve the filter results already set.
    $filter_results = $this->values;

    $query = $this->chado_connection->select('1:stock', 'child');
    $query->addField('child', 'name', 'cross_name');

    // Add Mom to the query.
    $query->addJoin('LEFT', '1:stock_relationship', 'relmom', 'child.stock_id = relmom.object_id');
    $query->addJoin('LEFT', '1:stock', 'mom', 'relmom.subject_id = mom.stock_id');
    $query->condition('relmom.type_id', $this->getRelationshipType('mom'), 'IN');
    $query->addField('mom', 'name', 'mom_name');

    // Add Dad to the query.
    $query->addJoin('LEFT', '1:stock_relationship', 'reldad', 'child.stock_id = reldad.object_id');
    $query->addJoin('LEFT', '1:stock', 'dad', 'reldad.subject_id = dad.stock_id');
    $query->condition('reldad.type_id', $this->getRelationshipType('dad'), 'IN');
    $query->addField('dad', 'name', 'dad_name');

    // Now we add the where arguments based on the filter results.
    // NOTE: make your placeholders match the key in the $filter_results array.
    // - Cross Name.
    if (!empty($filter_results['cross_name'])) {
      $query->condition('child.name', $filter_results['cross_name'], '~');
    }

    // - Maternal Parent.
    if (!empty($filter_results['mom_name'])) {
      $query->condition('mom.name', $filter_results['mom_name'], '~');
    }

    // - Paternal Parent.
    if (!empty($filter_results['dad_name'])) {
      $query->condition('dad.name', $filter_results['dad_name'], '~');
    }

    $query->orderBy('child.name', 'ASC');

    // Add the offset to the query for paging.
    if ($offset and is_numeric($offset)) {
      $query->range($offset, 26);
    }
    else {
      $query->range(0, 26);
    }
  }

  /**
   * Retrieves the cvterm_id for a relationship type.
   *
   * @param string $relationship
   *   Either 'mom' or 'dad' depending on which relationship type you want.
   *
   * @return array
   *   An array of cvterm_ids returned by the query.
   */
  protected function getRelationshipType(string $relationship) {
    $query = $this->chado_connection->select('1:cvterm', 'cvt')
      ->fields('cvt', ['cvterm_id']);
    if ($relationship === 'mom') {
      $query->condition('cvt.name', 'maternal', '~');
    }
    elseif ($relationship === 'dad') {
      $query->condition('cvt.name', 'paternal', '~');
    }
    $result = $query->execute();
    return $result->fetchCol();
  }

}
