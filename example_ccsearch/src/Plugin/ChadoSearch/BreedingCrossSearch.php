<?php

namespace Drupal\example_ccsearch\Plugin\ChadoSearch;

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
 *    require_submit = TRUE,
 *    pager = TRUE,
 *    num_items_per_page = 25,
 *  )
 */
class BreedingCrossSearch extends TripalIdSpaceBase {

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

}
