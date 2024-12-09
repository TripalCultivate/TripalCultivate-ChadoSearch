<?php

namespace Drupal\chado_search\ChadoSearch;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for chado_search plugins.
 */
abstract class ChadoSearchPluginBase extends PluginBase implements ChadoSearchInterface {

  /**
   * Add CSS/JS to the form/results page.
   *
   * NOTE: paths supplied should be relative to $module.
   *
   * @var array
   */
  public static array $attached = [
    'css' => [],
    'js' => [],
  ];

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static $info = [
    // Lists the columns in your results table.
    'fields' => [
      'column_name' => [
        'title' => 'Title',
        // This keyval is optional. It's used to make the current
        // column a link. The link is made automagically as long as
        // you add the id_column to your query.
        'entity_link' => [
          'chado_table' => 'feature',
          'id_column' => 'feature_id',
        ],
      ],
    ],
    // The filter criteria available to the user.
    // This is used to generate a search form which can be altered.
    'filters' => [
      'column_name' => [
        'title' => 'Title',
        'help' => 'A description for users as to what this filter is.',
      ],
    ],
  ];

  /**
   * The values submitted through the filter form by the user.
   *
   * @var array
   */
  public array $values;

  /**
   * Whether the user clicked the search button.
   *
   * @var bool
   */
  public bool $submitted;

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
